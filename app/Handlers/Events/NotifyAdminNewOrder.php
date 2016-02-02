<?php namespace App\Handlers\Events;

use App\Events\OrderScheduled;

use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class NotifyAdminNewOrder {

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
	 * @param  OrderScheduled  $event
	 * @return void
	 */
	public function handle(OrderScheduled $event)
	{
		try {
			//get assigned worker and andrew & dan
			$admins = User::whereIn('id', [1,2])->get();

			foreach($admins as $admin) {
				$event->twilio->message($admin->phone, trans('messages.order.new_schedule_order', [
					'order_service' => $event->order->service->name,
					'order_id' => $event->order->id,
					'scheduled_day' => $event->order->scheduled_day().", ".$event->order->scheduled_date(),
					'scheduled_time' => $event->order->scheduled_time(),
				]));
			}
		} catch(\Exception $e) {
			\Bugsnag::notifyException(new \Exception($e->getMessage()));
		}
	}

}
