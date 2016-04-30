<?php namespace App\Handlers\Events;

use App\Events\OrderAssign;

use App\Squeegy\PushNotification;

class NotifyCustomerAssign extends BaseEventHandler {

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
	 * @param  OrderAssign  $event
	 * @return void
	 */
	public function handle($event)
	{
		$push_message = trans('messages.order.push_notice.assign', [
			'worker_name'=>$event->order->worker->first_name(),
			'window_time'=>$event->order->arrival_eta(),
		]);

        if($event->order->schedule && $event->order->schedule->window_open) {
            $push_message = trans('messages.order.push_notice.schedule_assign', [
                'worker_name'=>$event->order->worker->first_name(),
                'window_time'=>$event->order->scheduled_time(),
            ]);
        }

        if($event->order->schedule && $event->order->schedule->type=='subscription') {
            return;
//            $push_message = trans('messages.order.push_notice_subscription.assign', [
//                'worker_name'=>$event->order->worker->name,
//                'window_time'=>$event->order->schedule->display_day()." @ ".$event->order->scheduled_time(),
//            ]);
        }

		if($event->order->location['zip'] == '90015') {
			$push_message = trans('messages.order.push_notice_corp.assign', [
				'worker_name'=>$event->order->worker->first_name(),
			]);
		}

		$arn_endpoint = ($event->order->push_platform=="apns" ? "push_token" : "target_arn_gcm" );

		if( ! PushNotification::send($event->order->customer->{$arn_endpoint}, $push_message, 1, $event->order->id, $event->order->push_platform, 'Order Status')) {
			//send sms to customer
			try {
				$twilio = \App::make('Aloha\Twilio\Twilio');
				$push_message = $this->_text_msg.$push_message;
				$twilio->message($event->order->customer->phone, $push_message);
			} catch(\Exception $e) {
				\Bugsnag::notifyException($e);
			}
		}
	}

}
