<?php namespace App\Handlers\Events;

use Aloha\Twilio\Twilio;
use App\Events\OrderScheduled;
use App\Notification;
use Illuminate\Support\Facades\Log;

class NotifyCustomerSchedule extends BaseEventHandler {

    public $twilio;
    public $delivery_method='sms';
    public $message;
    public $message_key = 'messages.order.push_notice.schedule';

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
	 * @param  OrderScheduled  $event
	 * @return void
	 */
	public function handle(OrderScheduled $event)
	{
		$this->message = trans($this->message_key);

		if($event->order->isSubscription()) {
            $this->message_key = 'messages.order.push_notice_subscription.schedule';
            $this->message = trans($this->message_key, [
				'subsription_schedule_time' => $event->order->schedule->start_date_time(),
			]);
		}

        if($event->order->partner) {
            $this->message_key = 'messages.order.push_notice_corp.schedule';
            $this->message = trans($this->message_key, [
				'schedule_day' => $event->order->schedule->window_open->format('l, F jS'),
			]);
		}

        $notification = Notification::where('key', $this->message_key)->first();

        if( ! $event->order->notification_logs()->where('notification_id', $notification->id)->count()) {

            try {
                $this->message = $this->_text_msg.$this->message;
                $this->twilio->message($event->order->customer->phone, $this->message);
            } catch (\Exception $e) {
                \Bugsnag::notifyException($e);
                return;
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
