<?php namespace App\Handlers\Events;

use App\Events\OrderScheduled;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class NotifyCustomerSchedule {

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  OrderScheduled  $event
	 * @return void
	 */
	public function handle(OrderScheduled $event)
	{
		//
	}

}
