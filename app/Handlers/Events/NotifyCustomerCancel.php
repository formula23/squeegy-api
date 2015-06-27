<?php namespace App\Handlers\Events;

use App\Events\OrderCancelled;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Support\Facades\Auth;
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
		if(Auth::user()->is('customer')) return; //If the customer cancelled their own order don't need to notify

        $push_message = trans('messages.order.push_notice.cancel');

        PushNotification::send($event->order, $push_message);

	}

}
