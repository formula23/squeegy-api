<?php namespace App\Events;

use App\Events\Event;
use App\User;
use Aloha\Twilio\Twilio;
use Illuminate\Queue\SerializesModels;

class UserRegistered extends Event {

	use SerializesModels;

    public $twilio;

    /**
     * Create a new event instance.
     *
     * @param Twilio $twilio
     * @internal param $user_id
     */
	public function __construct()
	{
        $this->twilio = \App::make('Twilio');
	}

}
