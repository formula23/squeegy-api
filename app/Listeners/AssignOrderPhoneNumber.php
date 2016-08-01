<?php

namespace App\Listeners;

use App\Events\OrderConfirmed;
use App\Order;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Services_Twilio as Twilio;

class AssignOrderPhoneNumber
{
    protected $twilio;

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
     * @param  OrderConfirmed  $event
     * @return void
     */
    public function handle($event)
    {

        ///get phone numbers currently in use
        $in_use_numbers = $event->order->phone_numbers_in_use()->toArray();

        \Log::info($in_use_numbers);

        ///get phone numbers available from twilio
        $all_available_incoming=[];
        foreach($this->twilio->account->incoming_phone_numbers->getIterator(0, 50, [
            'FriendlyName'=>config('twilio.twilio.connections.twilio.order_communication.friendly_name')
        ]) as $number)
        {
            $all_available_incoming[] = $number->phone_number;
        }

        \Log::info($all_available_incoming);

        $available_incoming = array_diff($all_available_incoming, $in_use_numbers);

        \Log::info($available_incoming);
        
        if( ! count($available_incoming) ) {
            \Log::info('get new number..');
            if($order_number = $this->getNewTwilioNumber()) {
                $event->order->phone = $order_number;
            }
        } else {
            $event->order->phone = array_shift($available_incoming);
        }
    }

    private function getNewTwilioNumber()
    {
        $numbers = $this->twilio->account->available_phone_numbers->getList('US', 'Local', array(
            "VoiceEnabled" => "true",
            "SmsEnabled" => "true"
        ));
        
        $twilioNumber = $numbers->available_phone_numbers[0]->phone_number;

        $numberSid = $this->twilio->account->incoming_phone_numbers->create(array(
            "PhoneNumber" => $twilioNumber,
            "FriendlyName" => config('twilio.twilio.connections.twilio.order_communication.friendly_name'),
            "SmsApplicationSid" => config('twilio.twilio.connections.twilio.application_sid'),
            "VoiceApplicationSid" => config('twilio.twilio.connections.twilio.application_sid')
        ));

        if ($numberSid)
        {
            return $twilioNumber;
        }
        else
        {
            return 0;
        }
    }
    
}
