<?php namespace App\Events;

use App\Events\Event;
use App\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class UserRegistered extends Event {

	use SerializesModels;

    public $twilio;
	public $user;
    /**
     * Create a new event instance.
     *
     * @param Twilio $twilio
     */
	public function __construct(User $user)
	{
        $this->twilio = \App::make('Aloha\Twilio\Twilio');
		$this->user = $user;
	}

}
