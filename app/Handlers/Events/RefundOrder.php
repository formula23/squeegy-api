<?php namespace App\Handlers\Events;

use App\Events\OrderCancelledByWorker;
use App\Squeegy\Payments;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class RefundOrder {

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
	 * @param  OrderCancelledByWorker  $event
	 * @return void
	 */
	public function handle(OrderCancelledByWorker $event)
	{
        $payments = new Payments($event->order->customer->stripe_customer_id);
        $charge = $payments->refund($event->order->stripe_charge_id);

        $event->order->charged -= $charge->amount;
        $event->order->refund = $charge->amount;
        $event->order->stripe_charge_id = $charge->id;
        $event->order->save();
	}

}
