<?php

namespace App\Console\Commands;

use Aloha\Twilio\Twilio;
use App\Notification;
use App\NotificationLog;
use App\Order;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class WasherTipNotify extends Command
{

    protected $twilio;
    public $message;
    public $delivery_method = 'sms';
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
        $users = User::workers()->where('is_active', 1)->get();

        foreach($users as $user) {

            $washer_tips=[];

            $tip_date = (false ? '2016-09-16' : Carbon::yesterday()->toDateString());

            $orders = $user->orders()
                ->where('tip', '>', 0)
                ->where('orders.status', 'done')
                ->whereNull('partner_id')
                ->whereDate('tip_at', '=', $tip_date)
                ->get();

            foreach($orders as $order) {
                $washer_tips[$order->id] = (round($order->tip * (1 - 0.029)) - 30)/100;
            }

            if( ! count($washer_tips)) continue;

            $user_tip_amt = array_sum($washer_tips);

            $this->create_message($user, $user_tip_amt);

            $notification = Notification::where('key', $this->message_key)->first();

            if( ! $user->received_tip_notification_for_date(Carbon::now()->toDateString())) {
                try {
                    $this->twilio->message($user->phone, $this->message);

                    $user->notifications()->create([
                        'notification_id'=>$notification->id,
                        'order_id'=>0,
                        'message'=>$this->message,
                        'delivery_method'=>$this->delivery_method,
                    ]);

                } catch (\Exception $e) {
                    \Bugsnag::notifyException($e);
                    \Log::info($e);
                }
            }
            
        }
    }

    private function create_message($user, $tip_amount) {

        $this->message = trans($this->message_key.".salutation", ['washer_name'=>$user->name])
            .trans($this->message_key.".body", ['tip_amt'=>'$'.number_format($tip_amount, 2)]);

        switch ($tip_amount) {
            case ($tip_amount>10 && $tip_amount<=25):
                $this->message .= trans($this->message_key.".motivation.10-25");
                break;
            case ($tip_amount>25 && $tip_amount<=40):
                $this->message .= trans($this->message_key.".motivation.25-40");
                break;
            case ($tip_amount>40):
                $this->message .= trans($this->message_key.".motivation.40");
                break;
            default:
        }

        $this->message .= trans($this->message_key.".sig");
    }
}
