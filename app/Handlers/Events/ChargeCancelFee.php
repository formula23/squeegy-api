<?php namespace App\Handlers\Events;

use App\Events\OrderCancelled;
use App\Squeegy\Orders;
use App\Squeegy\Payments;
use Carbon\Carbon;
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

        try {

			$transaction = $event->order->auth_transaction;
			$stripe_charge_id = ($transaction ? $transaction->charge_id : $event->order->stripe_charge_id);
			$credit = $event->order->order_credit;

			$order_charged = $event->order->charged + (!empty($credit->amount) ? abs($credit->amount) : 0 );

//			$cancel_fee = min(config('squeegy.cancellation_fee'), $order_charged);
			$cancel_fee = config('squeegy.cancellation_fee');

			Log::info("stripe charge id: ".$stripe_charge_id);
			Log::info("credit: ".$credit);
			Log::info("status: ".$event->order->getOriginal('status'));

			//don't charge cancel fee if washer has past eta or is cancelled within 5 mins of enroute.

			if( $this->full_refund($event->order) ) { //refund full
				Log::info('Full REFUND');
				$type = "void";

				if($credit) {
					Log::info('Credit exists..void the credit');
					$credit->status = $type;
					$credit->save();
				}

				$amount = $event->order->getOriginal('charged');
				Log::info('Charged amount:'.$amount);

				//full refund
				$event->order->charged = 0;

				if($stripe_charge_id) {
					Log::info('Refund Charge id: '.$stripe_charge_id);
					Log::info('Refund amount: '.$amount);

					$payments = new Payments($event->order->customer->stripe_customer_id);
					$charge = $payments->refund($stripe_charge_id);

					$txn_data = [
						'charge_id'=>$charge->id,
						'amount'=>$amount,
						'type'=>$type,
						'last_four'=>$charge->source->last4,
						'card_type'=>$charge->source->brand,
					];

					Log::info('Create txn rec: ', $txn_data);

					$event->order->transactions()->create($txn_data);

				}

			} else { //attempt to capture
				Log::info('Capture cancel fee');

				$type = "capture";

				if($credit) {
					Log::info('Credit exists...');
					Log::info('Cancel fee: '.$cancel_fee);
					Log::info('Credit amount: '.abs($credit->amount));

					if($cancel_fee > abs($credit->amount)) {
						Log::info('cancel fee > than credit amount');

						$available_credit = $event->order->customer->availableCredit() + abs($credit->amount);
						Log::info('Available Credit: '.$available_credit);

						if($available_credit) {
							Log::info('There is avialable credit, use as much as possible.');
							$credit->amount = -min($cancel_fee, $available_credit);
						}

						$credit->status = $type;
						$credit->save();
						$cancel_fee += $credit->amount;
						Log::info('cancel fee balanace: '.$cancel_fee);
					} else {
						Log::info('cancel fee < credit amount');
						$credit->amount = -$cancel_fee;
						$credit->status = $type;
						$credit->save();
						$cancel_fee=0;

						Log::info('Cancel fee was captured from credits.. refund credit card charge.');
						//if there was a charge refund it
						if($stripe_charge_id) {
							Log::info('Charge id: '.$stripe_charge_id);

							$payments = new Payments($event->order->customer->stripe_customer_id);
							$charge = $payments->refund($stripe_charge_id);
							$amount = $event->order->getOriginal('charged');

							$txn_data = [
								'charge_id'=>$charge->id,
								'amount'=>$amount,
								'type'=>"void",
								'last_four'=>$charge->source->last4,
								'card_type'=>$charge->source->brand,
							];
							Log::info('Create txn rec: ', $txn_data);
							$event->order->transactions()->create($txn_data);
						}
					}
				}

				if($cancel_fee) {
					Log::info('after credits, we have cancel fee balance..');
					$payments = new Payments($event->order->customer->stripe_customer_id);
					$charge = $payments->cancel($stripe_charge_id, $cancel_fee);
					Log::info('capture cancel balance...'.$cancel_fee);

					$amount = $cancel_fee;
					$event->order->charged = $cancel_fee;

					$txn_data = [
						'charge_id'=>$charge->id,
						'amount'=>$amount,
						'type'=>$type,
						'last_four'=>$charge->source->last4,
						'card_type'=>$charge->source->brand,
					];

					Log::info('Create txn rec: ', $txn_data);

					$event->order->transactions()->create($txn_data);
				}
			}

			Log::info('save order');

			$event->order->save();

        } catch(\Exception $e) {
			Log::error($e->getMessage());
            \Bugsnag::notifyException($e);
        }

	}

    protected function full_refund($order)
    {
		if( $this->order_seq[$order->getOriginal('status')] == 5 ) return false;

        if( $this->order_seq[$order->getOriginal('status')] < 4 ) {
            \Log::info('FULL REFUND - order not enroute yet');
            return true;
        }

        if( $order->eta && $order->assign_at->addMinutes($order->eta)->isPast() && $this->order_seq[$order->getOriginal('status')] < 5 ) {
            \Log::info('FULL REFUND - we missed the ETA.');
            return true;
        }

        if( $order->isSchedule() && $order->schedule->window_close->isPast() ) {
            \Log::info('FULL REFUND - we are after ending time slot.');
            return true;
        }

        if( $order->enroute_at && $order->enroute_at->addMinutes(5)->isFuture() ) {
            \Log::info('FULL REFUND - cancel is 5 mins within enroute');
            return true;
        }

        return false;
    }

}
