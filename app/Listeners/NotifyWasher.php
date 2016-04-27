<?php

namespace App\Listeners;

use Aloha\Twilio\Twilio;
use App\Events\ChangeWasher;
use App\Handlers\Events\BaseEventHandler;
use App\Squeegy\PushNotification;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class NotifyWasher extends BaseEventHandler
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
        $msg_data = [
            'order_id' => $event->order->id,
            'order_service' => $event->order->service->name,
        ];

        $twilio = \App::make('Aloha\Twilio\Twilio');

        foreach(['original_washer', 'new_washer'] as $washer_type) {

            switch ($washer_type) {
                case "original_washer":
                    $message = trans('messages.order.push_notice.change_washer.'.$washer_type, $msg_data);
                    $worker = User::find($event->order->getOriginal('worker_id'));

                    break;
                case "new_washer":
                    $message = trans('messages.order.push_notice.change_washer.'.$washer_type, $msg_data);
                    $worker = $event->order->worker;
                    break;
            }

            try {
                $twilio->message($worker->phone, $message);
            } catch (\Exception $e) {
                \Bugsnag::notifyException($e);
            }
        }
    }
}
