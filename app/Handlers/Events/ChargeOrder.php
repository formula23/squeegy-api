<?php namespace App\Handlers\Events;

use App\DiscountCode;
use App\Events\OrderDone;
use App\Squeegy\Payments;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class ChargeOrder {

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
        try {
            $order_amount = $event->order->price - (int)$event->order->discount;

            $payments = new Payments($event->order->customer->stripe_customer_id);

            if($event->order->stripe_charge_id) {
                $charge = $payments->capture($event->order->stripe_charge_id);
                $event->order->charged = $order_amount;
                $event->order->stripe_charge_id = $charge->id;
            }

            $event->order->save();

            if($event->order->discount_record->single_use_code) {
                $discount_code = DiscountCode::where('discount_id', $event->order->discount_id)->where('code', $event->order->promo_code)->get()->first();
                $discount_code->is_active = 0;
                $discount_code->save();
            }

        } catch(\Exception $e) {
            \Bugsnag::notifyException(new \Exception($e->getMessage()));
        }
	}

}
