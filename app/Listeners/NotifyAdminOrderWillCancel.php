<?php

namespace App\Listeners;

use Aloha\Twilio\Twilio;
use App\Events\OrderWillCancel;
use App\Notification;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAdminOrderWillCancel
{
    public $twilio;
    public $delivery_method='sms';
    public $message;
    public $message_key = 'messages.order.push_notice_schedule.will_cancel';

    /**
     * Create the event listener.
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
     * @param  OrderWillCancel  $event
     * @return void
     */
    public function handle(OrderWillCancel $event)
    {
        $this->message = trans($this->message_key, [
                'order_id'=>$event->order->id,
//                'cancel_time'=>$event->order->schedule->window_close->format('H:i'),
            ]);

        $notification = Notification::where('key', $this->message_key)->first();

        try {
            $additional_usersids = [1,2,6119];
            if(env('APP_ENV')!='production') $additional_usersids=[1];
            $admins = User::whereIn('id', $additional_usersids)->get();

            foreach($admins as $admin) {

                if( ! $event->order->notification_logs()->where('notification_id', $notification->id)->where('user_id', $admin->id)->count()) {

                    $this->twilio->message($admin->phone, $this->message);

                    $event->order->notification_logs()->create([
                        'notification_id' => $notification->id,
                        'user_id' => $admin->id,
                        'message' => $this->message,
                        'delivery_method' => $this->delivery_method,
                    ]);

                }
            }
            
        } catch (\Exception $e) {
            \Bugsnag::notifyException($e);
            return;
        }

    }
}
