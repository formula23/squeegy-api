<?php namespace App\Handlers\Events;

use App\Events\OrderScheduled;

class NotifyCustomerSchedule {

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
	 * @param  OrderScheduled  $event
	 * @return void
	 */
	public function handle(OrderScheduled $event)
	{
		$push_message = trans('messages.order.push_notice.schedule');
		try {
			$push_message = "Squeegy Order Status: ".$push_message;
			$event->twilio->message($event->order->customer->phone, $push_message);
		} catch (\Exception $e) {
			\Bugsnag::notifyException($e);
		}
	}

}
