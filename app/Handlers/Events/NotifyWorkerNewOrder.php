<?php namespace App\Handlers\Events;

use App\Events\OrderConfirmed;

use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

/**
 * Class NotifyWorkerNewOrder
 * @package App\Handlers\Events
 */
class NotifyWorkerNewOrder {

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
        try {

            $worker_qry = User::workers();

            $workers = $worker_qry->get();

            foreach($workers as $worker) {
                $event->twilio->message($worker->phone, trans('messages.order.new_order_worker', [
                    'order_id' => $event->order->id,
                    'customer_name' => $event->order->customer->name,
                    'customer_phone' => $event->order->customer->phone,
                ]));
            }
        } catch(\Exception $e) {
            \Bugsnag::notifyException(new \Exception($e->getMessage()));
        }


	}

}
