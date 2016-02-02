<?php namespace App\Handlers\Events;

use App\Events\OrderCancelled;
use App\Squeegy\Orders;
use App\Squeegy\Payments;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;


class ChargeCancelFee {

	protected $order_seq=null;

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->order_seq = Config::get('squeegy.order_seq');
	}

	/**
	 * Handle the event.
	 *
	 * @param  OrderCancelled  $event
	 * @return void
	 */
	public function handle($event)
	{
        $cancel_fee = min(config('squeegy.cancellation_fee'), $event->order->charged);

        try {
			$transaction = $event->order->auth_transaction;
			$stripe_charge_id = ($transaction ? $transaction->charge_id : $event->order->stripe_charge_id);

            if($stripe_charge_id) {
                $payments = new Payments($event->order->customer->stripe_customer_id);

				///if status is not enroute, start
				if($this->order_seq[$event->order->getOriginal('status')] < 4) {
					$amount = $event->order->getOriginal('charged');
					//full refund
					$event->order->charged = 0;
					$charge = $payments->refund($stripe_charge_id);
					$type = "void";
				} else {
					$charge = $payments->cancel($stripe_charge_id, $cancel_fee);
					$type = "capture";
					$amount = $cancel_fee;
					$event->order->charged = $cancel_fee;
				}

				$event->order->transactions()->create([
					'charge_id'=>$charge->id,
					'amount'=>$amount,
					'type'=>$type,
					'last_four'=>$charge->source->last4,
					'card_type'=>$charge->source->brand,
				]);

				$event->order->save();
            }

        } catch(\Exception $e) {
            \Bugsnag::notifyException($e);
        }

	}

}
