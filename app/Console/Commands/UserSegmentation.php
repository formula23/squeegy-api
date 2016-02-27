<?php namespace App\Console\Commands;

use App\Segment;
use App\User;
use App\UserSegment;
use Casinelli\CampaignMonitor\Facades\CampaignMonitor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UserSegmentation extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'user:segment';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Segment all the users.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$segments = Segment::all()->lists('id','name');

		$subscriber = CampaignMonitor::subscribers(Config::get('campaignmonitor.master_list_id'));

		User::chunk(1000, function($users) use ($segments, $subscriber) {

			$all_subscriber_data=[];

			foreach($users as $user)
			{
				if( ! $user->segment) {

					$user_segment = new UserSegment([
						'segment_id' => $segments["User"],
						'user_at' => $user->created_at,
					]);

					$orders_qry = $user->completedPaidOrders();
					$orders = $orders_qry->get();

					$first_order = $orders->first();
					$last_order = $orders->last();

					if($last_order) $user_segment->last_wash_at = $last_order->done_at;

					if($orders->count() == 1) {
						$user_segment->segment_id = $segments["Customer"];
						$user_segment->customer_at = $first_order->done_at;

					} else if($orders->count() >= 2) {
						$user_segment->segment_id = $segments["Repeat Customer"];
						$user_segment->customer_at = $first_order->done_at;
						$user_segment->repeat_customer_at = $orders[1]->done_at;
					}

					$referral_orders_qry = $user->referral_orders()->where('status', 'done')->orderBy('done_at');
					$referral_orders = $referral_orders_qry->get();

					if($user->is_advocate() && $user_segment->segment_id != 5) {
						$user_segment->segment_id = $segments["Advocate"];
						$user_segment->advocate_at = $referral_orders->first()->done_at;
					}

					if( ! $user->is_anon()) {
						$subscriber_data = [
							'EmailAddress' => $user->email,
							'Name' => $user->name,
							'CustomFields' => [
								['Key'=>'SegmentID', 'Value'=>$user_segment->segment_id],
								['Key'=>'Device', 'Value'=>$user->device()],
								['Key'=>'LastWash', 'Value'=>($last_order?$last_order->done_at->format('Y/m/d'):'')],
                                ['Key'=>'AvailableCredit', 'Value'=>$user->availableCredit()/100],
							]
						];

						$all_subscriber_data[] = $subscriber_data;
					}

					$user->segment()->save($user_segment);

					$this->info('User id:'.$user->id." ---- ".$user_segment->segment->name);

				} else {
					$this->info('User id:'.$user->id." -- Already segmented:".$user->segment->segment->name);
				}

			}

			///Save subscribers to Campaign Monitor
			$import_resp = $subscriber->import($all_subscriber_data, false, false, false);

			if($import_resp->was_successful()) {
				$this->info('Import Success');
			} else {

				$this->info('Failed with code: '.$import_resp->http_status_code);
//				var_dump($import_resp->response);

				if($import_resp->response->ResultData->TotalExistingSubscribers > 0) {
					$this->info('Updated '.$import_resp->response->ResultData->TotalExistingSubscribers.' existing subscribers in the list');
				} else if($import_resp->response->ResultData->TotalNewSubscribers > 0) {
					$this->info('Added '.$import_resp->response->ResultData->TotalNewSubscribers.' to the list');
				} else if(count($import_resp->response->ResultData->DuplicateEmailsInSubmission) > 0) {
					$this->info(count($import_resp->response->ResultData->DuplicateEmailsInSubmission).' were duplicated in the provided array.');
				}

				$this->info('The following emails failed to import correctly.');
				var_dump($import_resp->response->ResultData->FailureDetails);
			}

		});

	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			//['example', InputArgument::REQUIRED, 'An example argument.'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			//['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}
