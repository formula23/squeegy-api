<?php namespace App\Handlers\Events;

use App\Events\OrderScheduled;

class NotifyCustomerSchedule {

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct(){}

	/**
	 * Handle the event.
	 *
	 * @param  OrderScheduled  $event
	 * @return void
	 */
	public function handle(OrderScheduled $event)
	{
		$push_message = trans('messages.order.push_notice.schedule');

		if($event->order->location['zip'] == '90015') {
			$push_message = trans('messages.order.push_notice_corp.schedule', [
				'schedule_day' => $event->order->schedule->window_open->format('l, F jS'),
			]);
		}

		try {
			$push_message = "Squeegy Order Status: ".$push_message;
			$event->twilio->message($event->order->customer->phone, $push_message);
		} catch (\Exception $e) {
			\Bugsnag::notifyException($e);
		}
	}

}
