<?php namespace App\Handlers\Events;

use App\Events\OrderAssign;

use App\Squeegy\PushNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class NotifyCustomerAssign {

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
	 * @param  OrderAssign  $event
	 * @return void
	 */
	public function handle(OrderAssign $event)
	{
		$push_message = trans('messages.order.push_notice.enroute', [
			'worker_name'=>$event->order->worker->name,
			'arrival_time'=>eta_real_time($event->order),
		]);

		if( ! PushNotification::send($event->order->customer->push_token, $push_message, 1, $event->order->id)) {
			//send sms to customer
			$twilio = \App::make('Aloha\Twilio\Twilio');
			$push_message = "Thanks for using Squeegy! $push_message";
			$twilio->message($event->order->customer->phone, $push_message);
		}
	}

}
