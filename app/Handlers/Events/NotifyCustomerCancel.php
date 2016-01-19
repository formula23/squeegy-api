<?php namespace App\Handlers\Events;

use App\Events\OrderCancelledByWorker;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use App\Squeegy\PushNotification;

class NotifyCustomerCancel {

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
	 * @param  OrderCancelled  $event
	 * @return void
	 */
	public function handle(OrderCancelledByWorker $event)
	{
        $push_message = trans('messages.order.push_notice.cancel');

		$arn_endpoint = ($event->order->push_platform=="apns" ? "push_token" : "target_arn_gcm" );

        PushNotification::send($event->order->customer->{$arn_endpoint}, $push_message, 1, $event->order->id, $event->order->push_platform, 'Order Info');

	}

}
