<?php

namespace App\Console\Commands;

use Aloha\Twilio\Twilio;
use App\NotificationLog;
use App\Order;
use App\User;
use Illuminate\Console\Command;

class WasherTipNotify extends Command
{

    protected $twilio;
    public $message;
    public $message_key = 'messages.washer.daily_tip';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'washer:tip-notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send SMS message to each washer every night at 8pm if they received tips for given day.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Twilio $twilio)
    {
        parent::__construct();
        $this->twilio = $twilio;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //query all tips for today.
        $users = User::workers()->where('is_active', 1)->get();

        foreach($users as $user) {

            $washer_tips=[];

            $orders = $user->orders()
                ->where('tip', '>', 0)
                ->where('orders.status', 'done')
                ->whereRaw("date_format(tip_at, '%m-%d-%Y') = '07-17-2016'")
//                ->whereRaw("date_format(tip_at, '%m-%d-%Y') = date_format(now(), '%m-%d-%Y')")
                ->get();

            foreach($orders as $order) {
                $washer_tips[$order->id] = (round($order->tip * (1 - 0.029)) - 30)/100;
            }

            if( ! count($washer_tips)) continue;

            //message

            $this->info($user->name);

            $user_tip_amt = array_sum($washer_tips);

            $this->message = trans($this->message_key, [
                'daily_tip'=>'$'.number_format($user_tip_amt, 2),
            ]);

            $this->info($this->message);

            try {
                $this->twilio->message($user->phone, $this->message);

            } catch (\Exception $e) {
                \Bugsnag::notifyException($e);
                \Log::info($e);
            }

            $this->info('--------');

        }

    }
}
