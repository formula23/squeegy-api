<?php

namespace App\Handlers\Events;

use App\Events\OrderDone;
use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateMixPanel
{
    protected $mixpanel;

    /**
     * Create the event listener.
     *
     * @param LaravelMixpanel $mixpanel
     */
    public function __construct(LaravelMixpanel $mixpanel)
    {
        $this->mixpanel = $mixpanel;
    }

    /**
     * Handle the event.
     *
     * @param  OrderDone  $event
     * @return void
     */
    public function handle(OrderDone $event)
    {
        $user = $event->user;

        $data = [
            "Available Credits"=>$user->availableCredit()/100,
            "Segment ID" =>$user->segment?$user->segment->segment_id:0,
            "Lash Wash At" => ( ! empty($user->segment->last_wash_at)?$user->segment->last_wash_at->toAtomString():""),
            "Lash Wash Type" => ( ! empty($user->lastWash()) ? $user->lastWash()->service->name : "" ),
        ];

        $this->mixpanel->people->set($user->id, $data);

        $this->mixpanel->people->trackCharge($user->id, $event->order->revenue()/100);
    }
}