<?php namespace App\Console\Commands;

use App\Segment;
use App\User;
use App\UserSegment;
use Illuminate\Console\Command;
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

		User::chunk(200, function($users) use ($segments) {

			foreach($users as $user)
			{
				if( ! $user->segment) {

					$user_segment = new UserSegment([
						'segment_id' => $segments["User"],
						'user_at' => $user->created_at,
					]);

					if( ! $user->orders->count()) {
						$user->segment()->save($user_segment);
						continue;
					}

					//
					$orders_qry = $user->completedPaidOrders();
					$orders = $orders_qry->get();

					if( ! $orders->count()) continue;

					$first_order = $orders->first();

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

					if($referral_orders->count()) {
						$user_segment->segment_id = $segments["Advocate"];
						$user_segment->advocate_at = $referral_orders->first()->done_at;
					}

					$user->segment()->save($user_segment);

					$this->info('User id:'.$user->id." -- ".$user_segment->segment->name);

				} else {
					$this->info('User id:'.$user->id." -- Already segmented:".$user->segment->segment->name);
				}
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
