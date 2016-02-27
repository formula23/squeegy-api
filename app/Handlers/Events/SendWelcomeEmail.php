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
			$user = $event->user;
            Mail::send('emails.welcome', [], function ($message) use ($user) {

                $headers = $message->getHeaders();

                $mergevars = [
                    'name'=>$user->name,
                ];

                $headers->addTextHeader('X-MC-MergeVars', json_encode($mergevars));
                $headers->addTextHeader('X-MC-Template', 'welcome');

                $message->from(config('squeegy.emails.from'), config('squeegy.emails.from_name'));
                $message->bcc(config('squeegy.emails.bcc'));

                $message->to($user->email, $user->name)->subject(trans('messages.emails.welcome.subject'));
            });
        } catch(\Exception $e) {
            \Bugsnag::notifyException(new \Exception($e->getMessage()));
        }
	}

}
