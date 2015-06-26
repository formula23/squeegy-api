<?php namespace App\Handlers\Events;

use App\Events\OrderEnroute;
use Auth;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use App\Squeegy\PushNotification;

class NotifyCustomerEnroute {

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct()
	{

	}

	/**
	 * Handle the event.
	 *
	 * @param  OrderEnroute  $event
	 * @return void
	 */
	public function handle(OrderEnroute $event)
	{

        $push_message = trans('messages.order.push_notice.enroute', ['worker_name'=>Auth::user()->name]);

        PushNotification::send($event->order, $push_message);
	}

}
