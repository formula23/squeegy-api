<?php namespace App\Handlers\Events;

use App\Events\OrderConfirmed;

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
        $event->twilio->message('+13106004938', trans('messages.order.new_order_worker', ['order_id' => $event->order->id]));
	}

}
