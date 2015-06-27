<?php namespace App\Handlers\Events;

use App\Events\OrderConfirmed;
use App\Squeegy\Payments;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class AuthOrder {

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
	 * @param  OrderConfirmed  $event
	 * @return void
	 */
	public function handle(OrderConfirmed $event)
	{
		//auth customer card
        $order_amount = $event->order->price - (int)$event->order->discount;

        $payments = new Payments($event->order->customer->stripe_customer_id);

        $charge = $payments->auth($order_amount);

        $event->order->charged = $order_amount;
        $event->order->stripe_charge_id = $charge->id;
        $event->order->save();
	}

}
