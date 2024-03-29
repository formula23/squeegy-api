<?php namespace App\Handlers\Events;

use App\Events\OrderConfirmed;

use App\User;

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
			$additional_usersids = [6119];
			if(env('APP_ENV')!='production') {
				$additional_usersids=[1];
			} else {
				if($event->order->isPartner()) $additional_usersids[] = 2;//Only send Andrew partner orders.
			}

			$workers = User::workers()
                ->where('id', $event->order->worker_id)
                ->orWhereIn('id', $additional_usersids)
                ->get();

            $vehicle = $event->order->vehicle;

            foreach($workers as $worker) {

				$msg_data = [
					'order_service' => $event->order->service->name,
					'order_id' => $event->order->id,
					'eta' => "Quoted ETA: ".$event->order->eta." | Arrival: ".eta_real_time($event->order),
					'vehicle' => "\n".$vehicle->year." ".$vehicle->make." ".$vehicle->model." ".$vehicle->color,
					'customer_name' => $event->order->customer->first_name(),
					'customer_phone' => $event->order->customer->phone,
                    'customer_address' => '',
					'customer_address_lnk' => "\n\ncomgooglemaps://?q=".$event->order->location['lat'].",".$event->order->location['lon']."&views=traffic",
				];

				if($event->order->isPartner()) {
					$msg_data['customer_address'] = $event->order->partner->name."\n".$event->order->partner->location['street']."\n";
				}

				if($event->order->schedule && $event->order->schedule->window_open->hour==8) {
                    $msg_data['customer_address'] = "\n\n".$event->order->location['street'].", ".( ! empty($event->order->location['city']) ? $event->order->location['city'] : "" )." ".$event->order->location['zip'];
                }

                $event->twilio->message($worker->phone, trans('messages.order.new_order_worker', $msg_data));
            }
        } catch(\Exception $e) {
            \Bugsnag::notifyException(new \Exception($e->getMessage()));
        }
	}
}
