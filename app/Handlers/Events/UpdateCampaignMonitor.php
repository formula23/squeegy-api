<?php namespace App\Handlers\Events;

use App\Events\UserUpdated;

use CampaignMonitor;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class UpdateCampaignMonitor {

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  UserUpdated  $event
	 * @return void
	 */
	public function handle(UserUpdated $event)
	{
		try {
			$customer = $event->user;

			$subscriber = CampaignMonitor::subscribers(Config::get('campaignmonitor.master_list_id'));

			if(empty($customer->email) || $customer->is_tmp_email()) return;

			$subscriber_data = [
				'EmailAddress' => $customer->email,
				'Name' => $customer->name,
				'CustomFields' => [
					['Key'=>'SegmentID', 'Value'=>$customer->segment->segment_id],
					['Key'=>'Device', 'Value'=>$customer->device()],
					['Key'=>'AvailableCredit', 'Value'=>$customer->availableCredit()/100],
				]
			];

			if( ! empty($event->order)) {
				$subscriber_data['CustomFields'][] = ['Key'=>'LastWash', 'Value'=>$event->order->done_at->format('Y/m/d')];
			}
            if(preg_match('/squeegyapp-tmp.com$/', $event->orig_email)) {
                $result = $subscriber->add($subscriber_data, false, false);                
            } else {
                $result = $subscriber->update($event->orig_email, $subscriber_data, false, false);    
            }

            if($result->http_status_code != 200) {
                Log::info("CM resp status code:".$result->http_status_code);
				$err_msg = "Campaign Monitor: ".(!empty($result->response->Code) ? $result->response->Code : '' )." -- ".( ! empty($result->response->Message) ? $result->response->Message : '');
                Log::info($err_msg);
				\Bugsnag::notifyException(new \Exception($err_msg));
			}

		} catch(\Exception $e) {
			Log::info($e);
			\Bugsnag::notifyException($e);
		}
	}

}
