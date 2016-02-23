<?php namespace App\Events;

use App\Events\Event;

use App\User;
use Illuminate\Queue\SerializesModels;

class UserCreated extends Event {

	use SerializesModels;

	public $user;

	/**
	 * Create a new event instance.
	 *
	 * @param User $user
	 */
	public function __construct(User $user)
	{
		$this->user = $user;
	}

}
