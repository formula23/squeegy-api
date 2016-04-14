<?php

namespace App\Console\Commands;

use App\Order;
use App\Squeegy\Emails\ReceiptEmail;
use App\User;
use Illuminate\Console\Command;

use CampaignMonitor;
use Config;

class CMTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cm:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Campaing Monitor Facade/Provider';

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

        $user = User::find(155);
        $order = Order::find(1009);

        dd(
            (new ReceiptEmail)->withData(['data' => $order])->sendTo($user)
        );


        $cm = CampaignMonitor::smartSend('ad8fc689-263b-4856-bc25-acad275475eb');

        $message = [
            'To' => [
                'dan@formula23.com'
            ],
            'Data' => [
                'vehicle' => "<img align=\"none\" height=\"206\" src=\"https://s3-us-west-1.amazonaws.com/com.octanela.squeegy/orders/1001.jpg\" style=\"width: 275px; height: 206px; margin: 0px;\" width=\"275\" />",
            ]
        ];

        dd($cm->send($message));
    }
}
