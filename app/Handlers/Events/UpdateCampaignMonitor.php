<?php namespace App\Handlers\Events;

use App\Events\UserUpdated;

use Casinelli\CampaignMonitor\Facades\CampaignMonitor;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
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

			$result = $subscriber->update($event->orig_email, $subscriber_data, false, false);

			if($result->http_status_code != 201) {
				$err_msg = "Campaign Monitor: ".$result->response->Code." -- ".$result->response->Message;
				\Bugsnag::notifyException(new \Exception($err_msg));
			}

		} catch(\Exception $e) {
			Log::info($e);
			\Bugsnag::notifyException($e);
		}
	}

}
