<?php namespace App\Handlers\Events;

use App\Events\OrderCancelled;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use Mail;

class SendCancelEmail {

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
        $email_content = [
            'name' => $event->order->customer->name,
        ];

        Mail::send('emails.cancellation', $email_content, function ($message) use ($event) {
            $message->to($event->order->customer->email, $event->order->customer->name)->subject(trans('messages.emails.cancel.subject'));
        });
	}

}
