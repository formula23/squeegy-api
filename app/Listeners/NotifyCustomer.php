<?php

namespace App\Listeners;

use App\Events\ChangeWasher;
use App\Squeegy\PushNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyCustomer
{
    /**
     * Create the event listener.
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
     * @param  ChangeWasher  $event
     * @return void
     */
    public function handle($event)
    {
        $push_message = trans('messages.order.push_notice.change_washer.customer',['worker_name'=>$event->order->worker->name]);

        $arn_endpoint = ($event->order->push_platform=="apns" ? "push_token" : "target_arn_gcm");

        if ( ! PushNotification::send($event->order->customer->{$arn_endpoint}, $push_message, 1, $event->order->id, $event->order->push_platform, 'Order Status')) {
            try {
                $twilio = \App::make('Aloha\Twilio\Twilio');
                $push_message = $this->_text_msg.$push_message;
                $twilio->message($event->order->customer->phone, $push_message);
            } catch (\Exception $e) {
                \Bugsnag::notifyException($e);
            }
        }
    }
}
