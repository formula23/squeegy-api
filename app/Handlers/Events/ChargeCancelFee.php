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
	public function handle(OrderCancelled $event)
	{
        $cancel_fee = min(config('squeegy.cancellation_fee'), $event->order->charged);

        try {
			Log::info('Start ChargeCancelFee');
			$transaction = $event->order->auth_transaction;
			$stripe_charge_id = ($transaction ? $transaction->charge_id : $event->order->stripe_charge_id);

			Log::info('Charge id: '.$stripe_charge_id);

            if($stripe_charge_id) {
                $payments = new Payments($event->order->customer->stripe_customer_id);

				///if status is not enroute, start

				if($this->order_seq[$event->order->status] < 4) {
					Log::info('Full refund');
					//full refund
					$event->order->charged = 0;
					$charge = $payments->refund($stripe_charge_id);
					$type = "void";
					Log::info($charge);
				} else {
					$charge = $payments->cancel($stripe_charge_id, $cancel_fee);
					Log::info('Capture: '.$cancel_fee);
					Log::info($charge);
					$type = "capture";
					$event->order->charged = $cancel_fee;
				}
				Log::info($type);
				$event->order->transactions()->create([
					'charge_id'=>$charge->id,
					'amount'=>$cancel_fee,
					'type'=>$type,
					'last_four'=>$charge->source->last4,
					'card_type'=>$charge->source->brand,
				]);

//                $event->order->stripe_charge_id = $charge->id;
				$event->order->save();
            }

        } catch(\Exception $e) {

            \Bugsnag::notifyException($e);
        }

	}

}
