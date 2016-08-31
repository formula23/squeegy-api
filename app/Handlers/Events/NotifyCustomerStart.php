<?php namespace App\Handlers\Events;

use Aloha\Twilio\Twilio;
use App\Events\OrderStart;
use App\Squeegy\PushNotification;
use App\Notification;

class NotifyCustomerStart extends BaseEventHandler {

	public $twilio;
	public $delivery_method='push';
	public $message;
	public $message_key = 'messages.order.push_notice.start';

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
	 * @param  OrderStart  $event
	 * @return void
	 */
	public function handle(OrderStart $event)
	{
		if($event->order->schedule && $event->order->schedule->type=='subscription') { //surpress notifications for subscribed orders
			return;
		}
		
		if($event->order->isPartner()) return;
        
        $this->message = trans($this->message_key,[
			'worker_name'=>$event->order->worker->first_name(),
			'etc_time' => real_time($event->order->start_at->addMinutes($event->order->etc)),
            'car' => $event->order->vehicle->full_name(),
		]);

		$arn_endpoint = ($event->order->push_platform=="apns" ? "push_token" : "target_arn_gcm");

        $notification = Notification::where('key', $this->message_key)->first();
        
        if( ! $event->order->notification_logs()->where('notification_id', $notification->id)->count()) {
            
            if (!PushNotification::send($event->order->customer->{$arn_endpoint}, $this->message, 1, $event->order->id, $event->order->push_platform, 'Order Status')) {
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
