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
            "Last Wash At" => ( ! empty($user->segment->last_wash_at)?$user->segment->last_wash_at->toAtomString():""),
            "Last Wash Type" => ( ! empty($user->lastWash()) ? $user->lastWash()->service->name : "" ),
        ];

        if ($partner = $user->partners->first()) {
            $data['Partner ID'] = $partner->id;
            $data['Partner Name'] = $partner->name;
        }
        
//        \Log::info('MixPanel User data...');
//        \Log::info($data);

        $this->mixpanel->people->set($user->id, $data);

        $this->mixpanel->people->trackCharge($user->id, $event->order->revenue()/100);
    }
}
