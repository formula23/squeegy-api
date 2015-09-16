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
        $cancel_fee = min(config('squeegy.cancellation_fee'), $event->order->charged);

        try{
            if($event->order->stripe_charge_id) {
                $payments = new Payments($event->order->customer->stripe_customer_id);

                //always refund full amount
                $charge = $payments->refund($event->order->stripe_charge_id);

//                if($event->order->status != 'confirm') {
//                    $charge = $payments->cancel($event->order->stripe_charge_id, $cancel_fee);
//                    $event->order->charged = $cancel_fee;
//                } else {
//                    $charge = $payments->refund($event->order->stripe_charge_id);
//                }

                $event->order->stripe_charge_id = $charge->id;
            }

            $event->order->save();

        } catch(\Exception $e) {
            \Bugsnag::notifyException(new \Exception($e->getMessage()));
        }



	}

}
