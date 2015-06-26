<?php namespace App\Events;

use App\Events\Event;

use App\Order;
use Illuminate\Queue\SerializesModels;

/**
 * Class OrderConfirmed
 * @package App\Events
 */
class OrderConfirmed extends Event {

	use SerializesModels;

    /**
     * @var Order
     */
    public $order;
    /**
     * @var
     */
    public $twilio;

    /**
     * Create a new event instance.
     *
     * @param Order $order
     */
	public function __construct(Order $order)
	{
		$this->order = $order;
        $this->twilio = \App::make('Aloha\Twilio\Twilio');
	}

}
