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
		$order = $event->order;

		//if there is credit store save to credit DB
		if($order->credit && !$order->order_credit) {
			$order->order_credit()->create([
				'user_id'=>$order->user_id,
				'amount'=> -($order->credit),
				'status'=>'auth',
			]);
		}

		//auth customer card
		if($order->total) { //auth the card
			$payments = new Payments($order->customer->stripe_customer_id);
			$charge = $payments->auth($order->total);

			$order->transactions()->create([
				'charge_id'=>$charge->id,
				'amount'=>$charge->amount,
				'type'=>'auth',
				'last_four'=>$charge->source->last4,
				'card_type'=>$charge->source->brand,
			]);

//			$event->order->stripe_charge_id = $charge->id;
		}

//        $order_amount = $event->order->price - (int)$event->order->discount;
        $order->charged = $charge->amount;

//        if($order_amount) {
//
//            $charge = $payments->auth($order_amount);
//            $event->order->stripe_charge_id = $charge->id;
//        }

        $order->save();
	}

}
