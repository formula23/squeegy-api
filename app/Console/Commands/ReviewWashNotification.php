<?php

namespace App\Console\Commands;

use App\Events\WashReview;
use App\Notification;
use App\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class ReviewWashNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:review_wash_notice';

    protected $notification_id = 1;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send customer a notification that their wash is almost complete and to go review the wash if they so choose.';

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
        $orders = Order::select('orders.*')->where('status', 'start')
            ->leftJoin('notification_logs', function ($join) {
                $join->on('orders.id', '=', 'notification_logs.order_id')
                    ->where('notification_id', '=', $this->notification_id);
            })
            ->where(DB::raw("date_add(start_at, INTERVAL etc-10 MINUTE)"), '<=', Carbon::now()->toDateTimeString())
            ->whereNull('notification_id')
            ->get();

        foreach($orders as $order) {
            try {
                Event::fire(new WashReview($order));
                
            } catch (\Exception $e) {
                \Bugsnag::notifyException($e);
            }
        }
    }
}
