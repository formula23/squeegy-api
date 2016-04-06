<?php namespace App\Handlers\Events;

use Casinelli\CampaignMonitor\Facades\CampaignMonitor;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SyncCampaignMonitor {

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct()
	{}

	/**
	 * Handle the event.
	 *
	 * @param  $event
	 * @return void
	 */
	public function handle($event)
	{
		try {
			Log::info("Start sync Campaing monitor");
			$customer = $event->user;

			$subscriber = CampaignMonitor::subscribers(Config::get('campaignmonitor.master_list_id'));

			$subscriber_data = [
				'EmailAddress' => $customer->email,
				'Name' => $customer->name,
				'CustomFields' => [
					['Key'=>'SegmentID', 'Value'=>$customer->segment->segment_id],
					['Key'=>'Device', 'Value'=>$customer->device()],
					['Key'=>'AvailableCredit', 'Value'=>$customer->availableCredit()/100],
					['Key'=>'ReferralCode', 'Value'=>$customer->referral_code],
				]
			];

			if( ! empty($event->order)) {
				$subscriber_data['CustomFields'][] = ['Key'=>'LastWash', 'Value'=>$event->order->done_at->format('Y/m/d')];
			}

			Log::info($subscriber_data);

			$result = $subscriber->add($subscriber_data, false, true);

			if($result->http_status_code != 201) {
				$err_msg = "Campaign Monitor: ".$result->http_status_code." -- ".( ! empty($result->response) ? $result->response->Message : "");
				\Bugsnag::notifyException(new \Exception($err_msg));
			}

		} catch(\Exception $e) {
			Log::info($e);
			\Bugsnag::notifyException($e);
		}
	}

}
