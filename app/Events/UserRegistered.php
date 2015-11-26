<?php namespace App\Events;

use App\Events\Event;
use App\User;
use Illuminate\Queue\SerializesModels;

class UserRegistered extends Event {

	use SerializesModels;

    public $twilio;

    /**
     * Create a new event instance.
     *
     * @param Twilio $twilio
     */
	public function __construct()
	{
        $this->twilio = \App::make('Aloha\Twilio\Twilio');
	}

}
