<?php namespace App\Events;

use App\Events\Event;
use App\Order;
use Illuminate\Queue\SerializesModels;

/**
 * Class OrderStart
 * @package App\Events
 */
class OrderStart extends Event {

	use SerializesModels;

    /**
     * @var Order
     */
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
