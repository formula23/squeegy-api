<?php namespace App\Events;

use App\Events\Event;

use App\Order;
use Illuminate\Queue\SerializesModels;

class OrderScheduled extends Event {

	use SerializesModels;

	public $order;
	public $twilio;

	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct(Order $order)
	{
		$this->order = $order;
		$this->twilio = \App::make('Aloha\Twilio\Twilio');
	}

}