<?php namespace App\Handlers\Events;

use App\Events\OrderCancelled;
use Illuminate\Support\Facades\Auth;
use App\Squeegy\Payments;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;


class ChargeCancelFee {

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
        if(Auth::user()->is('worker')) return; //If worker cancelled do not charge the card.

        $cancel_fee = config('squeegy.cancellation_fee');

        $payments = new Payments($event->order->customer->stripe_customer_id);

        $charge = $payments->cancel($event->order->stripe_charge_id, $cancel_fee);

        $event->order->charged = $cancel_fee;
        $event->order->stripe_charge_id = $charge->id;
        $event->order->save();

	}

}
