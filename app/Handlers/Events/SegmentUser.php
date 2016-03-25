<?php namespace App\Handlers\Events;

use Carbon\Carbon;
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
			Log::info("Start Segment User....");
			$customer =& $event->user;
			$segment = $customer->segment;

			if( ! $segment) {
				$customer->segment()->create([
					'segment_id' => 2,
					'user_at' => Carbon::now(),
				]);
				$customer->load('segment');
			}

			$order =& $event->order;

			if( ! $order) return;

			if($order->referrer && $order->referrer->segment->segment_id != 5 && $order->referrer->is_advocate()) {
				$order->referrer->segment->segment_id = 5;
				$order->referrer->segment->advocate_at = $order->done_at;
			}

			$segment->last_wash_at = $order->done_at;

			if( ! $order->generated_revenue()) return;

			$paid_orders = $customer->completedPaidOrders()->count();

			if( ! $paid_orders) {
				if($segment->segment_id < 3) {
					$segment->segment_id = 3;
					$segment->customer_at = $order->done_at;
				}
			} else {
				if($segment->segment_id < 4) {
					$segment->segment_id = 4;
					$segment->repeat_customer_at = $order->done_at;
				}
			}

			Log::info($segment);

		} catch (\Exception $e) {
			\Log::info($e);
			\Bugsnag::notifyException($e);
		}

	}

}
