<?php namespace App\Handlers\Events;

use App\Events\OrderEnroute;
use Auth;
use Carbon\Carbon;
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
        $push_message = trans('messages.order.push_notice.enroute', [
            'worker_name'=>$event->order->worker->name,
            'arrival_time'=>eta_real_time($event->order),
        ]);

        PushNotification::send($event->order->customer->push_token, $push_message, 1, $event->order->id);
	}

}
