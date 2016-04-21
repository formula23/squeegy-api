<?php namespace App\Handlers\Events;

use App\DiscountCode;
use App\Events\OrderDone;
use App\Squeegy\Payments;
use Illuminate\Support\Facades\Log;

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
            Log::info("start charge order");
            $order =& $event->order;

            //if order has credits - capture them
            if($order->order_credit) {
                $order->order_credit->status = 'capture';
            }
            
            $transaction = $order->auth_transaction;
            $stripe_charge_id = ($transaction ? $transaction->charge_id : $event->order->stripe_charge_id);

            if($stripe_charge_id) {
                $payments = new Payments($event->order->customer->stripe_customer_id);

                $amt_to_capture = $order->charged;

                if($amt_to_capture > $transaction->amount) {
                    $additional_charge = $order->charged - $transaction->amount;

                    $amt_to_capture = $order->charged - $additional_charge;

                    $charge = $payments->sale($additional_charge, $order);
                    $order->transactions()->create([
                        'charge_id'=>$charge->id,
                        'amount'=>$charge->amount,
                        'type'=>'sale',
                        'last_four'=>$charge->source->last4,
                        'card_type'=>$charge->source->brand,
                    ]);
                }

                $charge = $payments->capture($stripe_charge_id, $amt_to_capture);
                $order->transactions()->create([
                    'charge_id'=>$charge->id,
                    'amount'=>$charge->amount,
                    'type'=>'capture',
                    'last_four'=>$charge->source->last4,
                    'card_type'=>$charge->source->brand,
                ]);
                $order->charged = $order->total;

            }

            if($order->discount_record && $order->discount_record->single_use_code) {
                $discount_code = DiscountCode::where('discount_id', $order->discount_id)->where('code', $order->promo_code)->get()->first();
                $discount_code->is_active = 0;
                $discount_code->save();
            }
            Log::info("end charge order");
        } catch(\Exception $e) {
            \Bugsnag::notifyException(new \Exception($e->getMessage()));
        }
	}

}
