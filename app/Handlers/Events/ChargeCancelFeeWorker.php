<?php namespace App\Handlers\Events;

use App\Events\OrderCancelledByWorker;
use App\Squeegy\Payments;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class ChargeCancelFeeWorker {

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
        $cancel_fee = min(config('squeegy.cancellation_fee'), $event->order->charged);

        try{
            $payments = new Payments($event->order->customer->stripe_customer_id);
            $charge = $payments->cancel($event->order->stripe_charge_id, $cancel_fee);

            $event->order->stripe_charge_id = $charge->id;
            $event->order->charged = $cancel_fee;
            $event->order->save();

        } catch(\Exception $e) {
            \Bugsnag::notifyException(new \Exception($e->getMessage()));
        }
	}

}
