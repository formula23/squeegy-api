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
			$customer = $event->user;

			$subscriber = CampaignMonitor::subscribers(Config::get('campaignmonitor.master_list_id'));

			$subscriber_data = [
				'EmailAddress' => $customer->email,
				'Name' => $customer->name,
				'CustomFields' => [
					['Key'=>'SegmentID', 'Value'=>$customer->segment->segment_id],
					['Key'=>'Device', 'Value'=>$customer->device()],
				]
			];

			if( ! empty($event->order)) {
				$subscriber_data['CustomFields'][] = ['Key'=>'LastWash', 'Value'=>$event->order->done_at->format('Y/m/d')];
			}

			Log::info($subscriber_data);

			$result = $subscriber->add($subscriber_data, false, true);

			Log::info('CM result:', $result);

		} catch(\Exception $e) {
			Log::info($e);
			\Bugsnag::notifyException($e);
		}
	}

}
