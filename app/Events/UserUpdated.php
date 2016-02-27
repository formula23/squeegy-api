<?php namespace App\Events;

use App\Events\Event;

use App\User;
use Illuminate\Queue\SerializesModels;

class UserUpdated extends Event {

	use SerializesModels;
	public $user;
	public $orig_email;
	/**
	 * Create a new event instance.
	 *
	 * @param User $user
	 */
	public function __construct($original_email, User $user)
	{
		$this->orig_email = $original_email;
		$this->user = $user;
	}

}
