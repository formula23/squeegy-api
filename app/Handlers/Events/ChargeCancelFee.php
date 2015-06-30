<?php namespace App\Handlers\Events;

use App\Events\OrderCancelled;
use App\Squeegy\Orders;
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
        $cancel_fee = config('squeegy.cancellation_fee');

        $payments = new Payments($event->order->customer->stripe_customer_id);

        if(Orders::getCurrentEta($event->order) < 30) {
            $charge = $payments->cancel($event->order->stripe_charge_id, $cancel_fee);
        } else {
            $charge = $payments->refund($event->order->stripe_charge_id);
        }

        $event->order->charged = $cancel_fee;
        $event->order->stripe_charge_id = $charge->id;
        $event->order->save();

	}

}
