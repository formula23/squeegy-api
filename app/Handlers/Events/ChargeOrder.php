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
            $order = $event->order;

            //if order has credits - capture them
            if($order->order_credit) {
                $order->order_credit->status = 'capture';
            }
            //if order->total exists capture

            $transaction = $order->transactions()->where('type', 'auth')->get()->first();

            if($transaction->charge_id) {
                $payments = new Payments($event->order->customer->stripe_customer_id);
                $charge = $payments->capture($transaction->charge_id);
                $order->transactions()->create([
                    'charge_id'=>$charge->id,
                    'amount'=>$charge->amount,
                    'type'=>'capture',
                    'last_four'=>$charge->source->last4,
                    'card_type'=>$charge->source->brand,
                ]);
            }

            $order->charged = $order->total;
            $order->push();

            if($order->discount_record && $order->discount_record->single_use_code) {
                $discount_code = DiscountCode::where('discount_id', $order->discount_id)->where('code', $order->promo_code)->get()->first();
                $discount_code->is_active = 0;
                $discount_code->save();
            }

        } catch(\Exception $e) {
            \Bugsnag::notifyException(new \Exception($e->getMessage()));
        }
	}

}
