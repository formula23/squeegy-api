<?php namespace App\Handlers\Events;

use App\Events\OrderCancelled;
use App\Squeegy\Orders;
use App\Squeegy\Payments;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Support\Facades\Config;


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

        try{
            if($event->order->stripe_charge_id) {
                $payments = new Payments($event->order->customer->stripe_customer_id);

				///if status is not enroute, start

				if($this->order_seq[$event->order->status] < 4) {
					//full refund
					$charge = $payments->refund($event->order->stripe_charge_id);
				} else {
					$payments = new Payments($event->order->customer->stripe_customer_id);
					$charge = $payments->cancel($event->order->stripe_charge_id, $cancel_fee);

					$event->order->stripe_charge_id = $charge->id;
					$event->order->charged = $cancel_fee;
					$event->order->save();

					$event->order->transactions()->create([
						'charge_id'=>$charge->id,
						'amount'=>$cancel_fee,
						'type'=>'capture',
						'last_four'=>$charge->source->last4,
						'card_type'=>$charge->source->brand,
					]);
				}

                $event->order->stripe_charge_id = $charge->id;
            }

            $event->order->save();

        } catch(\Exception $e) {
            \Bugsnag::notifyException($e);
        }

	}

}
