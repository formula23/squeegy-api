<?php

namespace App\Events;

use App\Events\Event;
use App\Order;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChangeWasher extends Event
{
    use SerializesModels;

    public $order;

    public $message;

    public $message_key = "messages.order.push_notice.change_washer.customer";
    
    /**
     * Create a new event instance.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;

        $this->message = trans($this->message_key,['worker_name'=>$order->worker->name]);
        
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
