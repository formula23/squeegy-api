<?php

namespace App\Console\Commands;

use App\Order;
use App\Squeegy\Emails\Receipt;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class EmailCustomerReceipt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:send_receipt {order_ids}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email customer a receipt for a given order number';

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
        if( ! $this->argument('order_ids')) {
            $this->error('Order Id(s) required');
            return;
        }

        $orders = Order::whereIn('id', explode(",", $this->argument('order_ids')))->get();

        $orders->map(function ($order) {
            $this->send_email($order);
        });
    }

    protected function send_email($order) {

        try {
            (new Receipt)
                ->withBCC(config('squeegy.emails.bcc'))
                ->withData(['data' => $order])
                ->sendTo($order->customer);
        } catch(\Exception $e) {
            \Bugsnag::notifyException($e);
        }
    }

}
