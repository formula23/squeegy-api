<?php namespace App\Handlers\Events;

use App\Events\UserRegistered;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Support\Facades\Mail;
use Auth;

class SendWelcomeEmail {


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
	 * @param  UserRegistered  $event
	 * @return void
	 */
	public function handle(UserRegistered $event)
	{
        try {
            Mail::send('emails.welcome', [], function ($message) {
                $message->to(Auth::user()->email, Auth::user()->name)->subject(config('squeegy.emails.welcome.subject'));
            });
        } catch(\Exception $e) {
            \Bugsnag::notifyException(new \Exception($e->getMessage()));
        }
	}

}
