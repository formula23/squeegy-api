<?php namespace App\Handlers\Events;

use Aloha\Twilio\Twilio;
use App\Events\OrderEnroute;
use Auth;
use App\Squeegy\PushNotification;
use App\Notification;

class NotifyCustomerEnroute extends BaseEventHandler {


	public $twilio;
	public $delivery_method='push';
	public $message;
	public $message_key = 'messages.order.push_notice.enroute';


	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct(Twilio $twilio)
	{
        $this->twilio = $twilio;
	}

	/**
	 * Handle the event.
	 *
	 * @param  OrderEnroute  $event
	 * @return void
	 */
	public function handle(OrderEnroute $event)
	{

		if($event->order->schedule && $event->order->schedule->type=='subscription') { //surpress notifications for subscribed orders
			return;
		}

		$msg_key = ($event->auto) ? "enroute" : "enroute_manual" ;

		if( ! $event->auto) { //get real travel time
			$arrival_time = current_eta($event->order);
		} else {
			$arrival_time = eta_real_time($event->order);
		}

        $this->message_key = 'messages.order.push_notice.'.$msg_key;
        $this->message = trans($this->message_key, [
            'worker_name'=>$event->order->worker->first_name(),
            'arrival_time'=>$arrival_time,
        ]);

		if($event->order->location['zip'] == '90015') {
            $this->message_key = 'messages.order.push_notice_corp.enroute';
			$this->message = trans($this->message_key, [
				'worker_name'=>$event->order->worker->first_name(),
				'interior'=>$event->order->service_id == 2 ? "Please open your vehicle if it is not already." : "",
			]);
		}

		$arn_endpoint = ($event->order->push_platform=="apns" ? "push_token" : "target_arn_gcm" );

        $notification = Notification::where('key', $this->message_key)->first();

        if( ! $event->order->notification_logs()->where('notification_id', $notification->id)->count()) {

            if( ! PushNotification::send($event->order->customer->{$arn_endpoint}, $this->message, 1, $event->order->id, $event->order->push_platform, 'Order Status')) {
                //send sms to customer
                try {
                    $this->message = $this->_text_msg.$this->message;
                    $this->twilio->message($event->order->customer->phone, $this->message);
                    $this->delivery_method = 'sms';
                } catch (\Exception $e) {
                    \Bugsnag::notifyException($e);
                    return;
                }
            }

            try {
                $event->order->notification_logs()->create([
                    'notification_id'=>$notification->id,
                    'user_id'=>$event->order->user_id,
                    'message'=>$this->message,
                    'delivery_method'=>$this->delivery_method,
                ]);
            } catch (\Exception $e) {
                \Bugsnag::notifyException($e);
            }

        }
	}
}
