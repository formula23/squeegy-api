<?php

namespace App\Console\Commands;

use App\Order;
use App\Squeegy\Emails\Receipt;
use Illuminate\Console\Command;

class EmailCustomerReceipt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:send_receipt {order}';

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

        $order = Order::find($this->argument('order'));
dd($order);
        try {
            (new Receipt)
                ->withBCC(config('squeegy.emails.bcc'))
                ->withData(['data' => $order])
//                ->sendTo($order->customer);
                ->sendTo("dan@squeegyapp.com");

        } catch(\Exception $e) {
            \Bugsnag::notifyException($e);
        }

    }
}
