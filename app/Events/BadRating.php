<?php namespace App\Events;

use App\Events\Event;
use App\Order;
use Illuminate\Queue\SerializesModels;

class BadRating extends Event {

	use SerializesModels;

    public $order;

    /**
     * Create a new event instance.
     *
     * @param Order $order
     */
	public function __construct(Order $order)
	{
		$this->order = $order;
	}

}
