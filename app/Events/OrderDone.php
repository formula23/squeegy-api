<?php namespace App\Events;

use App\Order;
use Illuminate\Queue\SerializesModels;

class OrderDone extends Event {

	use SerializesModels;

    public $order;
	public $user;

    /**
     * Create a new event instance.
     *
     * @param Order $order
     */
	public function __construct(Order $order)
	{
		$this->order = $order;
		$this->user = $order->customer;
	}

}
