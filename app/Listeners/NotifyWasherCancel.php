<?php

namespace App\Listeners;

use Aloha\Twilio\Twilio;
use App\Events\OrderCancelled;
use App\Notification;
use App\Squeegy\PushNotification;

class NotifyWasherCancel
{
    public $twilio;
    public $delivery_method='sms';
    public $message;
    public $message_key = 'messages.order.push_notice.cancel_washer';

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
     * @param  OrderCancelled  $event
     * @return void
     */
    public function handle(OrderCancelled $event)
    {
        if( ! $event->order->worker_id ) return; //No one to notify..

        $this->message = trans($this->message_key, [
            'order_id'=>$event->order->id,
            'vehicle'=>$event->order->vehicle->full_name(),
        ]);

        $notification = Notification::where('key', $this->message_key)->first();

        if( ! $event->order->notification_logs()->where('notification_id', $notification->id)->count()) {

            try {
                $this->twilio->message($event->order->worker->phone, $this->message);

                $event->order->notification_logs()->create([
                    'notification_id' => $notification->id,
                    'user_id' => $event->order->worker_id,
                    'message' => $this->message,
                    'delivery_method' => $this->delivery_method,
                ]);
                
            } catch (\Exception $e) {
                \Bugsnag::notifyException($e);
                return;
            }
            
        }
        
    }
}
