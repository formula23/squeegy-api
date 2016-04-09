<?php

namespace App\Console\Commands;

use App\User;
use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class ImportMixpanel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:import-mixpanel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all the users to mixpanel and attribute their orders.';

    protected $mixpanel;

    /**
     * Create a new command instance.
     *
     * @param LaravelMixpanel $mixpanel
     */
    public function __construct(LaravelMixpanel $mixpanel)
    {
        parent::__construct();

        $this->mixpanel = $mixpanel;

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        User::customers()->where('email','tz6@test.com')->chunk(1000, function($users) {

            foreach($users as $user) {
//                $this->info($user->id);
                $user->save();

                foreach($user->completedPaidOrders()->get() as $order) {

                    $charged = $order->charged;

                    if(in_array($order->discount_id, array_keys($promo_prices = Config::get('squeegy.groupon_gilt_promotions')))) {
                        $charged = $promo_prices[$order->discount_id];
                    }

                    $this->mixpanel->people->trackCharge($user->id, ($charged/100), $order->done_at->timestamp);
                }

                $this->info($user->id." -- ".$user->email." -- Updated");
            }

        });

    }
}
