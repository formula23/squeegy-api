<?php namespace App\Handlers\Events;

use App\Events\OrderCancelledByWorker;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use App\Squeegy\PushNotification;

class NotifyCustomerCancel extends BaseEventHandler {

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

		if ( ! PushNotification::send($event->order->customer->push_token, $push_message, 1, $event->order->id)) {
			$twilio = \App::make('Aloha\Twilio\Twilio');
			$push_message = $this->_text_msg.$push_message;
			$twilio->message($event->order->customer->phone, $push_message);
		}
	}

}
