<?php namespace App\Events;

use App\Events\Event;

use App\Order;
use Illuminate\Queue\SerializesModels;

class OrderAssign extends Event {

	use SerializesModels;

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
