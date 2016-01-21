<?php namespace App\Handlers\Events;

use App\Credit;
use App\Events\OrderDone;

use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Support\Facades\Config;

class CreditReferrer {

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
	 * @param  OrderDone  $event
	 * @return void
	 */
	public function handle(OrderDone $event)
	{
		if($event->order->referrer) {
			$credit = new Credit([
				'order_id'=>$event->order->id,
				'amount'=>Config::get('squeegy.referral_program.referrer_amt')
			]);
			if( ! Credit::where('order_id', $event->order->id)->get()->count()) {
				$event->order->referrer->credits()->save($credit);
			}
		}
	}

}
