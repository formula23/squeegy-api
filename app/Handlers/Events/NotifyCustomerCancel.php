<?php namespace App\Handlers\Events;

use App\Events\OrderCancelled;

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
	public function handle(OrderCancelled $event)
	{
        $push_message = trans('messages.order.push_notice.cancel');

        PushNotification::send($event->order, $push_message);

	}

}
