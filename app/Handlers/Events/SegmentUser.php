<?php namespace App\Handlers\Events;

use App\Events\UserRegistered;

use App\Segment;
use App\UserSegment;
use Carbon\Carbon;
use Casinelli\CampaignMonitor\Facades\CampaignMonitor;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SegmentUser {

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct() {}

	/**
	 * Handle the event.
	 *
	 * @param $event
	 * @return void
	 */
	public function handle($event)
	{

		try {
			$customer = $event->user;
			$segment = $customer->segment;

			if( ! $segment) {
				$user_segment = UserSegment::create([
					'segment_id' => 2,
					'user_at' => Carbon::now(),
				]);
				$customer->segment()->save($user_segment);
			}

			$order =& $event->order;

			if($order) {
				if($order->referrer && $order->referrer->segment->segment_id != 5 && $order->referrer->is_advocate()) {
					$order->referrer->segment->segment_id = 5;
					$order->referrer->segment->advocate_at = Carbon::now();
				}

				if( ! $order->generated_revenue()) return;

				$paid_orders = $customer->completedPaidOrders()->count();

				if( ! $paid_orders) {
					if($segment->segment_id < 3) {
						$segment->segment_id = 3;
						$segment->customer_at = Carbon::now();
					}
				} else {
					if($segment->segment_id < 4) {
						$segment->segment_id = 4;
						$segment->repeat_customer_at = Carbon::now();
					}
				}

			}

		} catch (\Exception $e) {
			\Bugsnag::notifyException($e);
		}

	}

}
