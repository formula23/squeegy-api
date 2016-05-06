<?php namespace App\Handlers\Events;

use Aloha\Twilio\Twilio;
use App\Events\OrderAssign;
use App\Notification;
use App\Squeegy\PushNotification;

class NotifyCustomerAssign extends BaseEventHandler {

    public $twilio;
    public $delivery_method='push';
    public $message;
    public $message_key = 'messages.order.push_notice.assign';

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
	 * @param  OrderAssign  $event
	 * @return void
	 */
	public function handle($event)
	{
        $this->message = trans($this->message_key, [
			'worker_name'=>$event->order->worker->first_name(),
			'window_time'=>$event->order->arrival_eta(),
		]);

        if($event->order->schedule && $event->order->schedule->window_open) {
            $this->message_key = 'messages.order.push_notice.schedule_assign';
            $this->message = trans($this->message_key, [
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

        if($event->order->partner) {
            $this->message_key = 'messages.order.push_notice_corp.assign';
            $this->message = trans($this->message_key, [
				'worker_name'=>$event->order->worker->first_name(),
			]);
        }

        $notification = Notification::where('key', $this->message_key)->first();
        
        if( ! $event->order->notification_logs()->where('notification_id', $notification->id)->count()) {

            $arn_endpoint = ($event->order->push_platform == "apns" ? "push_token" : "target_arn_gcm");

            if (!PushNotification::send($event->order->customer->{$arn_endpoint}, $this->message, 1, $event->order->id, $event->order->push_platform, 'Order Status')) {
                //send sms to customer
                try {
                    $this->message = $this->_text_msg . $this->message;
                    $this->twilio->message($event->order->customer->phone, $this->message);
                    $this->delivery_method = 'sms';
                } catch (\Exception $e) {
                    \Bugsnag::notifyException($e);
                    return;
                }
            }

            try {
                $event->order->notification_logs()->create([
                    'notification_id' => $notification->id,
                    'user_id' => $event->order->user_id,
                    'message' => $this->message,
                    'delivery_method' => $this->delivery_method,
                ]);
            } catch (\Exception $e) {
                \Bugsnag::notifyException($e);
            }
        }
		
	}

}
