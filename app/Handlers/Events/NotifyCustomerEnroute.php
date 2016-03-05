<?php namespace App\Handlers\Events;

use App\Events\OrderEnroute;
use App\Squeegy\Orders;
use Auth;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use App\Squeegy\PushNotification;

class NotifyCustomerEnroute {

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct()
	{

	}

	/**
	 * Handle the event.
	 *
	 * @param  OrderEnroute  $event
	 * @return void
	 */
	public function handle(OrderEnroute $event)
	{
		$msg_key = ($event->auto) ? "enroute" : "enroute_manual" ;

		if( ! $event->auto) { //get real travel time
			$arrival_time = current_eta($event->order);
		} else {
			$arrival_time = eta_real_time($event->order);
		}

        $push_message = trans('messages.order.push_notice.'.$msg_key, [
            'worker_name'=>$event->order->worker->name,
            'arrival_time'=>$arrival_time,
        ]);

		if($event->order->location['zip'] == '90015') {
			$push_message = trans('messages.order.push_notice_corp.enroute', [
				'worker_name'=>$event->order->worker->name,
				'interior'=>$event->order->service_id == 2 ? "Please open your vehicle if it is not already." : "",
			]);
		}

		$arn_endpoint = ($event->order->push_platform=="apns" ? "push_token" : "target_arn_gcm" );

		if( ! PushNotification::send($event->order->customer->{$arn_endpoint}, $push_message, 1, $event->order->id, $event->order->push_platform, 'Order Status')) {
			//send sms to customer
            try {
                $twilio = \App::make('Aloha\Twilio\Twilio');
                $push_message = "Squeegy Order Status: ".$push_message;
                $twilio->message($event->order->customer->phone, $push_message);
            } catch (\Exception $e) {
                \Bugsnag::notifyException($e);
            }

		}
	}

}
