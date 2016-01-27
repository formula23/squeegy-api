<?php namespace App\Handlers\Events;

use App\Events\OrderAssign;

use App\Squeegy\PushNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class NotifyCustomerAssign extends BaseEventHandler {

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
		$push_message = trans('messages.order.push_notice.assign', [
			'worker_name'=>$event->order->worker->name,
			'window_time'=>$event->order->scheduled_time(),
		]);

		if( ! PushNotification::send($event->order->customer->push_token, $push_message, 1, $event->order->id)) {
			//send sms to customer
			try {
				$twilio = \App::make('Aloha\Twilio\Twilio');
				$push_message = $this->_text_msg.$push_message;
				$twilio->message($event->order->customer->phone, $push_message);
			} catch(\Exception $e) {
				\Bugsnag::notifyException($e);
			}
		}
	}

}
