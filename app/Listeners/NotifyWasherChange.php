<?php

namespace App\Listeners;

use Aloha\Twilio\Twilio;
use App\Events\ChangeWasher;
use App\Handlers\Events\BaseEventHandler;
use App\Squeegy\PushNotification;
use App\User;
use App\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class NotifyWasherChange extends BaseEventHandler
{
    public $twilio;
    public $delivery_method = 'sms';
    public $message_key = "messages.order.push_notice.review_wash";
    public $message;
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
        
        $msg_data = [
            'order_id' => $event->order->id,
            'order_service' => $event->order->service->name,
        ];

        foreach(['original_washer', 'new_washer'] as $washer_type) {

            $message_key = 'messages.order.push_notice.change_washer.'.$washer_type;
            
            switch ($washer_type) {
                case "original_washer":
                    $message = trans($message_key, $msg_data);
                    $worker = User::find($event->order->getOriginal('worker_id'));
                    $notification = Notification::where('key', $message_key)->first();
                    break;
                
                case "new_washer":
                    $message = trans($message_key, $msg_data);
                    $worker = $event->order->worker;
                    $notification = Notification::where('key', $message_key)->first();
                    break;
            }

            try {

                $this->twilio->message($worker->phone, $message);
                
                $event->order->notification_logs()->create([
                    'notification_id'=>$notification->id,
                    'user_id'=>$worker->id,
                    'message'=>$message,
                    'delivery_method'=>$this->delivery_method,
                ]);
                
            } catch (\Exception $e) {
                \Bugsnag::notifyException($e);
            }
        }
    }
}
