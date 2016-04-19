<?php namespace App\Handlers\Events;

use App\Events\OrderDone;
use App\Squeegy\Emails\Receipt;
use Mail;
use Stripe\Card;
use Stripe\Customer;
use Stripe\Stripe;

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
     * @param OrderDone $event
     */
	public function handle(OrderDone $event)
	{
        $order = $event->order;

        try {

            (new Receipt)
                ->withBCC(config('squeegy.emails.bcc'))
				->withData(['data' => $order])
				->sendTo($order->customer);

        } catch(\Exception $e) {
            \Bugsnag::notifyException(new \Exception($e->getMessage()));
        }

	}

}
