<?php namespace App\Events;

use App\Events\Event;

use App\Order;
use Illuminate\Queue\SerializesModels;

class OrderScheduled extends Event {

	use SerializesModels;

	public $order;
    public $user;

	/**
	 * Create a new event instance.
	 *
	 * @param Order $order
	 * @param $user
	 */
	public function __construct(Order $order)
	{
		$this->order = $order;
        $this->user = $order->customer;
	}

}
