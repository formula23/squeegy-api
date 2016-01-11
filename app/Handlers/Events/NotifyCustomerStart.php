<?php namespace App\Handlers\Events;

use App\Events\OrderStart;
use App\Squeegy\PushNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class NotifyCustomerStart {

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
	 * @param  OrderStart  $event
	 * @return void
	 */
	public function handle(OrderStart $event)
	{
        $push_message = trans('messages.order.push_notice.start',['worker_name'=>$event->order->worker->name]);

        if ( ! PushNotification::send($event->order->customer->push_token, $push_message, 1, $event->order->id))
		{
			$twilio = \App::make('Aloha\Twilio\Twilio');
			$twilio->message($event->order->customer->phone, $push_message);
		}
	}

}
