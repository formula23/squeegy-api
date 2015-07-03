<?php namespace App\Handlers\Events;

use App\Events\OrderDone;
use Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class SendReceiptEmail {

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
        $order = $event->order;
        //send email
        $email_content = [
            'name' => $event->order->customer->name,
        ];

        try {
            Mail::send('emails.receipt', $email_content, function ($message) use ($order) {
                $message->to($order->customer->email, $order->customer->name)->subject(trans('messages.emails.receipt.subject'));
            });
        } catch(\Exception $e) {
            \Bugsnag::notifyException(new \Exception($e->getMessage()));
        }

	}

}
