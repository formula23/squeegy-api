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
	public function handle($event)
	{
        try {

            //get assigned worker and andrew & dan
            $workers = User::workers()
                ->where('id', $event->order->worker_id)
                ->orWhereIn('id', [1,2])
                ->get();

            $vehicle = $event->order->vehicle;

            foreach($workers as $worker) {
                $event->twilio->message($worker->phone, trans('messages.order.new_order_worker', [
                    'order_service' => $event->order->service->name,
                    'order_id' => $event->order->id,
                    'eta' => "Quoted ETA: ".$event->order->eta." | Arrival: ".eta_real_time($event->order),
                    'vehicle' => "\n".$vehicle->year." ".$vehicle->make." ".$vehicle->model." ".$vehicle->color,
                    'customer_name' => $event->order->customer->name,
                    'customer_phone' => $event->order->customer->phone,
                    'customer_address' => "\n\n".$event->order->location['street'].", ".$event->order->location['city']." ".$event->order->location['zip'],
                    'customer_address_lnk' => "\n\ncomgooglemaps://?q=".$event->order->location['lat'].",".$event->order->location['lon']."&views=traffic",
                ]));
            }
        } catch(\Exception $e) {
			dd($e);
            \Bugsnag::notifyException(new \Exception($e->getMessage()));
        }


	}

}
