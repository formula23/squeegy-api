<?php

namespace App\Console\Commands;

use App\Events\OrderAssign;
use App\Events\OrderCancelledByWorker;
use App\Events\OrderWillCancel;
use App\Order;
use App\Squeegy\Orders;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AssignScheduleWashes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:assign-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically assign scheduled orders to the appropriate washer';

    protected $lead_time_buffer = 15;

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
        $this->info(Carbon::now().' - Start assign scheduled wash process.');

        if( ! Orders::open()) {
            $this->info('Not open!');
            return;
        }

        $scheduled_orders = Order::ofStatus('schedule')
            ->with('schedule')
            ->whereDate('confirm_at', '=', Carbon::today()->toDateString())
            ->whereNull('partner_id')
            ->orderBy('confirm_at')
//            ->where('id', 6817)
            ->get();

        if( ! $scheduled_orders->count()) {
            $this->info('No scheduled orders at this time.');
            return;
        }

        foreach($scheduled_orders as $order) {

//            if($order->schedule->window_close->isPast()) {
//                $this->info('Order never got assigned and time has passed - cancel.');
//                $this->cancel_order($order);
//                continue;
//            }

            $this->info('Get availability for order id: ' . $order->id);

            $this->info($order->location['lat'] . ", " . $order->location['lon']);

            $avail = Orders::availability($order->location['lat'], $order->location['lon']);

            //no washers available...
            if(Carbon::now()->addMinutes(30)->gte($order->schedule->window_open) &&
                $avail['schedule'] &&
                empty($avail['actual_time']))
            {
                \Event::fire(new OrderWillCancel($order));
                continue;
            }

            $this->info(print_r($avail, 1));

            if ( ! empty($avail['actual_time'])) {

                $arrival_time = Carbon::now()->addMinutes($avail['actual_time'] + $this->lead_time_buffer);

                $this->info('Arrival time: ' . $arrival_time);
                $this->info('Scheduled time: ' . $order->confirm_at);

                if ($arrival_time->gt($order->confirm_at)) {
                    $this->info('Ok to assign...');
                    try {
                        $order->status = 'assign';
                        $order->assign_at = Carbon::now();
                        $order->eta = $avail['actual_time'];
                        $order->worker_id = $avail['worker_id'];
                        $order->save();
                        $this->info('Assigned!');
                    } catch (\Exception $e) {
                        $this->error('Error - Unable to assign order!');
                        \Bugsnag::notifyException($e);
                        continue;
                    }

                    \Event::fire(new OrderAssign($order));

                } else {
                    $this->info("don't assign yet...");
                }
            }
        }
    }

    protected function cancel_order($order)
    {
        try {
            $order->status = 'cancel';
            $order->cancel_at = Carbon::now();

            \Event::fire(new OrderCancelledByWorker($order));

            $order->save();

            $this->info('Order cancelled:'.$order->id);
            return;

        } catch(\Exception $e) {
            $this->error('Error - Unable able to cancel');
            \Bugsnag::notifyException($e);
            return;
        }
    }

}
