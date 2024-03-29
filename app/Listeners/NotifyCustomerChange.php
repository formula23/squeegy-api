<?php

namespace App\Listeners;

use Aloha\Twilio\Twilio;
use App\Events\ChangeWasher;
use App\Handlers\Events\BaseEventHandler;
use App\Squeegy\PushNotification;
use App\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyCustomerChange extends BaseEventHandler
{
    public $twilio;
    public $delivery_method='push';
    public $message;
    public $message_key = 'messages.order.push_notice.change_washer.customer'; 
    /**
     * Create the event listener.
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
     * @param  ChangeWasher  $event
     * @return void
     */
    public function handle($event)
    {
        $this->message = trans($this->message_key, [
            'worker_name'=>$event->order->worker->first_name()]
        );
        
        $arn_endpoint = ($event->order->push_platform=="apns" ? "push_token" : "target_arn_gcm");
        
        $notification = Notification::where('key', $this->message_key)->first();
        
        if ( ! PushNotification::send($event->order->customer->{$arn_endpoint}, $this->message, 1, $event->order->id, $event->order->push_platform, 'Order Status')) {
            
            try {
                $this->message = $this->_text_msg.$this->message;
                $this->twilio->message($event->order->customer->phone, $this->message);
                $this->delivery_method = 'sms';
            } catch (\Exception $e) {
                \Bugsnag::notifyException($e);
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
