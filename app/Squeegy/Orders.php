<?php namespace App\Squeegy;
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/15/15
 * Time: 18:18
 */

use App\Order;
use App\User;
use Carbon\Carbon;

/**
 * Class Orders
 * @package App\Squeegy
 */
class Orders {

    const CLOSING_THRESHOLD = 20;
    const BASE_LEAD_TIME = 20;
    const SUV_SURCHARGE = 500;
    const SUV_SURCHARGE_MULTIPLIER = 2;
    const TRAVEL_TIME = 20;

    /**
     * @var array
     */
    private static $order_status_time_map = [
        'confirm' => 50,
        'enroute' => 40,
        'start' => 30,
    ];

    /**
     * @return bool
     */
    public static function open()
    {
        if( ! env('OPERATING_OPEN')) return false;

        $now = Carbon::now();

//        $now = Carbon::create(2015,9,16,19,30,0);

        if($now->dayOfWeek == 0) return false;

        $open_time = Carbon::createFromTime(config('squeegy.operating_hours.open'), 0, 0);
        $close_time = Carbon::createFromTime(config('squeegy.operating_hours.close'), env('OPERATING_MIN_CLOSE', 0), 0);

//        $open_time = Carbon::create(2015,9,16,9,0,0);
//        $close_time = Carbon::create(2015,9,16,18,15,0);

        if($now >= $open_time && $now <= $close_time) return true;
        return false;

//        if(env('APP_DEV')) return true;
//        if(! env('OPERATING_WKND') && Carbon::now()->isWeekend()) return false;

    }

    /**
     * @return array
     */
    public static function availability() {

        $data = ['accept'=>self::open(), 'description'=>'', 'time'=>0, 'time_label'=>'', 'service_area' => config('squeegy.service_area')];

        if( ! self::open()) {

            if(env('MAINTENANCE')) {
                $data['accept'] = 0;
                $data['description'] = "Squeegy is currently closed for scheduled maintenance.";
                return $data;
            }

//            $now = Carbon::create(2015,9,16,19,30,0);
//            $open_time = Carbon::create(2015,9,16,9,0,0);
//            $close_time = Carbon::create(2015,9,16,18,15,0);

            $now = Carbon::now();

            $day_of_week = $now->dayOfWeek;
            $curr_hr = $now->hour;

            $next_day = ($curr_hr >= env('OPERATING_HR_CLOSE') && $curr_hr <= 23 || !env('OPERATING_WKND') ? $now->addDay()->format('l') : $now->format('l') );

            if($day_of_week == 6 && $curr_hr > env('OPERATING_HR_CLOSE') || $day_of_week == 0) {
                $next_day = "Monday";
            }

            $data['description'] = trans('messages.service.closed', ['next_day' => $next_day]);
        }

        $data['lead_time'] = self::getLeadTime();
        if( ! $data['lead_time']) {
            $data['accept'] = 0;
            $data['description'] = trans('messages.service.highdemand');
        }

        $lead_time_arr = Orders::formatLeadTime($data['lead_time']);

        return array_merge($data, $lead_time_arr);
    }

    /**
     * @param Order $order
     * @return int
     */
    public static function getPrice(Order $order)
    {
        return $order->service->price;

        $base_price = $order->service->price;

        switch($order->vehicle->type)
        {
            case "SUV":
                $base_price += self::SUV_SURCHARGE;
                break;
            case "SUV+":
            case "Truck":
            case "Van":
                $base_price += self::SUV_SURCHARGE * self::SUV_SURCHARGE_MULTIPLIER;
                break;
        }

        return $base_price;
    }

    /**
     * @param Order $order
     * @return mixed
     */
    public static function getCurrentEta(Order $order)
    {
        $dt = Carbon::now();
        $time_passed = $dt->diffInSeconds(new Carbon($order->confirm_at));
        return max(($order->eta * 60 - $time_passed), 0);
    }

    /**
     * Get the lead time to perform next order based on operating hours and open order status
     * Return time in minutes
     *
     * @param Order $order
     * @return int
     */
    public static function getLeadTime(Order $order = null)
    {

        //get available workers
        $available_workers = User::workers()
            ->select('users.*')
            ->where('users.is_active',1)
            ->leftJoin(\DB::raw("(select * from orders where orders.status in ('confirm', 'enroute', 'start')) AS orders"), 'users.id', '=', 'orders.worker_id')
            ->whereNull('orders.status')
            ->get()
            ->count();

        //jobs in Q
        $pending_orders = Order::where('status', 'confirm')->count();
//        print $orders_in_q;
//dd($available_workers);
        if($available_workers - $pending_orders > 0) {
            return static::TRAVEL_TIME;
        }

        $orders_in_q = Order::query();
        $orders_in_q->whereIn('status', ['confirm','enroute','start']);

        $open_orders = $orders_in_q->get();

        $orders_assessed=[];
        $completion_times=[];

//        $testdate = Carbon::create(2015,9,9,18);

        foreach(['start', 'enroute', 'confirm'] as $status) {
            foreach($open_orders as $orders) {

                if( ! empty($orders->{$status."_at"}) && ! in_array($orders->id, $orders_assessed)) {

                    $service_time = $orders->service->time;

                    $mins_elapsed = $orders->{$orders->status."_at"}->diffInMinutes();

//                    print $status." == ".$orders->service->name." | ".$orders->id."<br/>";
//                    print "mins elapsed: ".$mins_elapsed."<br/>";
//                    print "service time: ".$service_time."<br/>";

                    if($orders->status == "start") {
//                        print "should be done: ".max(0, $service_time - $mins_elapsed)."<br/>";
                        $complete_time = max(0, ($service_time - $mins_elapsed));
                    }

                    if($orders->status == "enroute") {
//                        print "alloted travel time:".self::TRAVEL_TIME."<br/>";
//                        print "elapsed travel time:".$mins_elapsed."<br/>";
//                        print "should be done: ".(max(0, self::TRAVEL_TIME - $mins_elapsed) + $service_time)."<br/>";
                        $complete_time = max(0, (self::TRAVEL_TIME - $mins_elapsed) + $service_time);
                    }

                    if($orders->status == "confirm") {
                        $complete_time = max(0, ($orders->eta - $mins_elapsed) + $service_time);
//                        print "should be done: ".$complete_time."<br/>";
                    }

                    $completion_times[] = $complete_time;

                    $orders_assessed[] = $orders->id;
                }
//print "<br/>";
            }
        }

        sort($completion_times);

//        print "ETA: ".($completion_times[$pending_orders] + self::TRAVEL_TIME);
//        dd($completion_times);
//exit;

        $order_index = ($pending_orders > $available_workers && $pending_orders > 0) ? $pending_orders : $pending_orders - 1 ;

        if($order_index < 0) $order_index = 0;
        if($order_index > count($completion_times)) $order_index = count($completion_times) - 1;

        return $completion_times[$order_index] + self::TRAVEL_TIME;
    }

    /**
     * @param $leadtime
     * @return string
     */
    public static function formatConfirmEta($leadtime) {

        if($leadtime < 60) {
            return $leadtime." minutes";
        }
        $hrs = floor($leadtime/60);
        return $hrs." ".str_plural("hour", $hrs)." ".($leadtime % 60)." mins";
    }

    /**
     * @param $leadtime
     * @return array
     */
    public static function formatLeadTime($leadtime)
    {
//        if($leadtime < 60) {
            return [
                'time'=>(string)$leadtime,
                'time_label'=>'mins'
            ];
//        }

//        $t = $leadtime/60;
//
//        return [
//            'time' => (string)(is_float($t) ? floor($t)."h".($leadtime % 60) : $t ),
//            'time_label'=>'hour'
//        ];
    }

    /**
     * Get the remaining business hours in minutes
     *
     * @return int
     */
    public static function remainingBusinessTime()
    {
        if(env('APP_DEV')) return 1000;
        return Carbon::createFromTime(\Config::get('squeegy.operating_hours.close'),0,0)->diffInMinutes();
    }

}