<?php namespace App\Handlers\Events;

use App\Events\OrderDone;
use App\Squeegy\PushNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class NotifyCustomerDone {

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
	 * @param  OrderComplete  $event
	 * @return void
	 */
	public function handle(OrderDone $event)
	{
        $push_message = trans('messages.order.push_notice.done',['worker_name'=>$event->order->worker->name, 'charge_amount'=>number_format($event->order->charged/100, 0)]);

        PushNotification::send($event->order, $push_message);

    }

}
