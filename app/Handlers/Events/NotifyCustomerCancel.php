<?php namespace App\Handlers\Events;

use Aloha\Twilio\Twilio;
use App\Events\OrderCancelledByWorker;
use App\Squeegy\PushNotification;
use App\Notification;

class NotifyCustomerCancel extends BaseEventHandler {

	public $twilio;
	public $delivery_method='push';
	public $message;
	public $message_key = 'messages.order.push_notice.cancel';

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
	 * @param  OrderCancelled  $event
	 * @return void
	 */
	public function handle(OrderCancelledByWorker $event)
	{
        if($event->order->isSchedule()) {
            $this->message_key = 'messages.order.push_notice_schedule.cancel';
        }

        $this->message = trans($this->message_key);

        $arn_endpoint = ($event->order->push_platform=="apns" ? "push_token" : "target_arn_gcm" );

        $notification = Notification::where('key', $this->message_key)->first();
        
        if( ! $event->order->notification_logs()->where('notification_id', $notification->id)->count()) {

            if ( ! PushNotification::send($event->order->customer->{$arn_endpoint}, $this->message, 1, $event->order->id, $event->order->push_platform, 'Order Info')) {
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
