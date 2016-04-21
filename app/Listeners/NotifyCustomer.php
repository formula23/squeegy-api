<?php

namespace App\Listeners;

use App\Events\ChangeWasher;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyCustomer
{
    /**
     * Create the event listener.
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
     * @param  ChangeWasher  $event
     * @return void
     */
    public function handle($event)
    {
//        throw new \Exception("asd");
        print $event->order->getOriginal('worker_id');
        print_r($event->order->customer);
    }
}
