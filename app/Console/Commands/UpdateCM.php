<?php

namespace App\Console\Commands;

use App\Order;
use App\User;
use App\UserSegment;
use CampaignMonitor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class UpdateCM extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:referral_code_cm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all email address in CM with their referral code.';

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

        $subscriber = CampaignMonitor::subscribers(Config::get('campaignmonitor.master_list_id'));

        Order::whereNotNull('partner_id')
            ->select('users.email', 'partners.id as partner_id', 'partners.name as partner_name')
            ->join('users','orders.user_id','=','users.id')
            ->join('partners', 'orders.partner_id', '=', 'partners.id')
            ->groupBy('users.id')
            ->where('status','done')
            ->chunk(10, function ($users) use ($subscriber) {

                foreach($users as $user) {
                    dd($user);
                    $subscriber_data=[
                        'CustomFields'=> [
                            ['Key'=>'PartnerID', 'Value'=>$user->partner_id],
                            ['Key'=>'PartnerName', 'Value'=>$user->partner_name],
                        ]
                    ];

                    $result = $subscriber->update($user->email, $subscriber_data);

                    if($result->http_status_code != 200) {
                        $err_msg = "Campaign Monitor: ".$result->http_status_code." -- ".( ! empty($result->response) ? $result->response->Message : "");
                        $this->error($err_msg);
                    } else {
                        $this->info($user->email." updated.");
                    }
                }

            });
dd("Done");


        User::customers()->chunk(1000, function($users) use ($subscriber) {

            foreach($users as $user)
            {
                $partner = $user->partners->first();
                if( ! $partner) continue;
                
                $subscriber_data=[
                    'CustomFields'=> [
                        ['Key'=>'PartnerID', 'Value'=>$partner->id],
                        ['Key'=>'PartnerName', 'Value'=>$partner->name],
                    ]
                ];

                $result = $subscriber->update($user->email, $subscriber_data);

                if($result->http_status_code != 200) {
                    $err_msg = "Campaign Monitor: ".$result->http_status_code." -- ".( ! empty($result->response) ? $result->response->Message : "");
                    $this->error($err_msg);
                } else {
                    $this->info($user->email." updated.");
                }

            }

        });

    }
}
