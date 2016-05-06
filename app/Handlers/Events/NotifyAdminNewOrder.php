<?php namespace App\Handlers\Events;

use Aloha\Twilio\Twilio;
use App\Events\OrderScheduled;

use App\User;

class NotifyAdminNewOrder {

	public $twilio;

	/**
	 * Create the event handler.
	 *
	 * @param Twilio $twilio
	 */
	public function __construct(Twilio $twilio)
	{
		$this->twilio = $twilio;
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
            $additional_usersids = [1,2,6119];
            if(env('APP_ENV')!='production') $additional_usersids=[1];
            $admins = User::whereIn('id', $additional_usersids)->get();

            $message = trans('messages.order.new_schedule_order', [
                'order_service' => $event->order->service->name,
                'order_id' => $event->order->id,
                'scheduled_day' => $event->order->scheduled_day().", ".$event->order->scheduled_date(),
                'scheduled_time' => $event->order->scheduled_time(),
                'location'=>($event->order->order->partner ? ' at '.$event->order->order->partner->name : '' ),
            ]);

            if($event->order->isSubscription()) {
                $message = trans('messages.order.new_subscription_schedule_order', [
                    'order_service' => $event->order->service->name,
                    'order_id' => $event->order->id,
                    'subsription_schedule_time' => $event->order->schedule->start_date_time(),
                ]);
            }

            foreach($admins as $admin) {
				$this->twilio->message($admin->phone, $message);
			}
		} catch(\Exception $e) {
			\Bugsnag::notifyException(new \Exception($e->getMessage()));
		}
	}

}
