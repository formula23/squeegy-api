<?php

namespace App\Console\Commands;

use App\Events\OrderDone;
use App\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;

class ChargeOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:charge {order_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Capture funds for given order';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $order = Order::find($this->argument('order_id'));
        if(!$order) {
            $this->error("Order id required");
            return;
        }
        Event::fire(new OrderDone($order));

    }
}
