<?php namespace App\Handlers\Events;

use Aloha\Twilio\Twilio;
use App\Events\UserRegistered;
use Auth;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class SendSMSVerification {

	/**
	 * Create the event handler.
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
     * @param  UserRegistered $event
     * @param Twilio $twilio
     */
	public function handle(UserRegistered $event)
	{
        try {
            $event->twilio->message(Auth::user()->phone, "Squeegy verification code: " . config('squeegy.sms_verification'));
        } catch(\Exception $e) {}
	}

}
