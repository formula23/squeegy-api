<?php

namespace App\Console\Commands;

use App\Order;
use App\Squeegy\Emails\Receipt;
use App\User;
use Illuminate\Console\Command;

use CampaignMonitor;
use Config;
use Mail;

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

//        $email_data=[];
//        Mail::send('payroll.email', ['washer'=>"Dan", 'week_of'=>"sadf"], function($message) use ($email_data)
//        {
//            $message->getHeaders()->addTextHeader('X-CMail-GroupName', 'Payroll');
//
//            $message->from('payments@squeegyapp.com', 'Squeegy Payments');
//            $message->replyTo('tech@squeegyapp.com', 'Squeegy');
//            $message->to('dan@squeegyapp.com', 'Dan Schultz');
//
//            $message->subject("Squeegy Pay - Week of ");
////            $message->attach($email_data['time_sheet']);
//        });
//        dd("sent.....");

        $user = User::find(4412);
        $order = Order::find(4770);
        
        dd(
            (new Receipt)->withData(['data' => $order])->sendTo($user)
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
