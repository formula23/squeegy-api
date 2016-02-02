<?php namespace App\Events;

use App\Events\Event;
use App\Order;
use Illuminate\Queue\SerializesModels;

class OrderEnroute extends Event {

	use SerializesModels;

    public $order;
	public $auto;

	/**
	 * Create a new event instance.
	 *
	 * @param Order $order
	 * @param bool $auto
	 */
	public function __construct(Order $order, $auto = true)
	{
		$this->order = $order;
		$this->auto = $auto;
	}

}
