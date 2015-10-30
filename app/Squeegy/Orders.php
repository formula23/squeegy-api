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
    const CLOSING_BUFFER = 10;

    protected static $travel_time = 40;
    protected static $open_orders;

    /**
     * @return bool
     */
    public static function open()
    {
        return true;
        if( ! env('OPERATING_OPEN') || env('MAINTENANCE')) return false;

        $now = Carbon::now();

//         $now = Carbon::create(2015,10,18,2,12,0);

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
     * @param null $lat
     * @param null $lng
     * @return array
     */
    public static function availability($lat=null, $lng=null) {

        $data = ['accept'=>self::open(), 'description'=>'', 'time'=>0, 'time_label'=>'', 'service_area' => config('squeegy.service_area')];

        if( ! self::open()) {

            if(env('MAINTENANCE')) {
                $data['accept'] = 0;
                $data['description'] = "Squeegy is currently closed for scheduled maintenance.";
                return $data;
            }

//            $now = Carbon::create(2015,10,18,2,12,0);
//            $open_time = Carbon::create(2015,9,16,9,0,0);
//            $close_time = Carbon::create(2015,9,16,18,15,0);

            $now = Carbon::now();

            $day_of_week = $now->dayOfWeek;
            $curr_hr = $now->hour;

            $next_day = ($curr_hr >= env('OPERATING_HR_CLOSE') && $curr_hr <= 23 || !env('OPERATING_WKND') ? $now->addDay()->format('l') : $now->format('l') );
            
            $next_day_is_monday = ($curr_hr >= env('OPERATING_HR_CLOSE') || ($curr_hr = env('OPERATING_HR_CLOSE') && $now->minute >= env('OPERATING_MIN_CLOSE')));
                
            if($day_of_week == 6 && $next_day_is_monday || $day_of_week == 0) {
                $next_day = "Monday";
            }

            $data['description'] = trans('messages.service.closed', ['next_day' => $next_day, 'close_mins'=>(env('OPERATING_MIN_CLOSE')=='00' ? 'pm' : ':'.env('OPERATING_MIN_CLOSE').'pm' )]);
        }
        
        $data['lead_time'] = self::getLeadTime(null, $lat, $lng);

        if(self::open() && self::$open_orders->count() >= 1 && $data['lead_time'] > (self::remainingBusinessTime() + self::CLOSING_BUFFER)) {
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
     * @param null $lat
     * @param null $lng
     * @return int
     */
    public static function getLeadTime(Order $order = null, $lat = null, $lng = null)
    {

        //geo-code lat-long
        if($lat && $lng) {


//            try {
//                $customer_postal="";
//                $response = \GoogleMaps::load('geocoding')
//                    ->setParam (['latlng' =>$lat.','.$lng])
//                    ->get();
//                $json_resp = json_decode($response);
//                if($json_resp->status =="OK") {
//                    foreach($json_resp->results as $add_comps) {
//                        foreach($add_comps->address_components as $add_comp) {
//                            if($add_comp->types[0]=="postal_code") {
//                                $customer_postal = $add_comp->long_name;
//                                break 2;
//                            }
//                        }
//                    }
//                }
//
//            } catch (\Exception $e) {
//
//            }
        }

        $total_active_workers = User::workers()->where('users.is_active', 1)->get();

//        print "total active workers:".$total_active_workers->count()."<br/>";

        self::setTravelTime($total_active_workers->count());

        $orders_in_q = Order::whereIn('status', ['confirm','enroute','start'])
            ->orderBy('worker_id')
            ->orderBy('confirm_at');

        self::$open_orders = $orders_in_q->get();

//        print "open orders: ".self::$open_orders->count()."<br/>";

//        $available_workers = $total_active_workers->count() - self::$open_orders->count();

        $unassigned_orders = Order::where('status', 'confirm')->get()->count();

//        print "un-assigned: ".$unassigned_orders."<br />";

        //get available workers
        $unassigned_active_workers = User::workers()
            ->select('users.*')
            ->where('users.is_active',1)
            ->leftJoin(\DB::raw("(select * from orders where orders.status in ('confirm', 'enroute', 'start')) AS orders"), 'users.id', '=', 'orders.worker_id')
            ->whereNull('orders.status')
            ->get()
            ->count();
//        dd($unassigned_available_workers - $unassigned_orders);

        $available_workers = ($unassigned_active_workers - $unassigned_orders);

//        dd("available workers: ".$available_workers);

        $lead_time = "total active workers: ".$total_active_workers->count()." \n\n available workers: $available_workers \n\n open orders: ".self::$open_orders->count();

        if($available_workers > 0) {
            return static::$travel_time;
        }

        ///completion times for each worker
//        print_r(self::$open_orders->toArray());
//        exit;
        $complete_times_by_worker=[];

//        print "travel time: ".static::$travel_time;

        foreach(self::$open_orders as $idx=>$order) {
//            print "idx: ".$idx."<br/>";
//            print_r($order->toArray());
//            if( ! isset($complete_times_by_worker[$order->worker_id])) $complete_times_by_worker[$order->worker_id] = 0;

            if($order->status == "start") {
                $complete_times_by_worker[$order->worker_id]['q'][] = max(5, $order->service->time - $order->start_at->diffInMinutes());

//                print $order['location']['street']." --> ".self::$open_orders[$idx+1]['location']['street'];

            } else {
                ///calc remaining travel time
                if( ! isset($complete_times_by_worker[$order->worker_id])) {
                    $complete_times_by_worker[$order->worker_id]['q'][] = max(5, static::$travel_time - $order->enroute_at->diffInMinutes());
                }
                $complete_times_by_worker[$order->worker_id]['q'][] = (int)$order->service->time;
            }

            ///TO-DO:
            /**
             *
             * Calculation travel time between $current location and $next_location (if exists)
             * For now... default to static travel time
             */

            $current_location = $order['location']['lat'].",".$order['location']['lon'];
            $next_location = (isset(self::$open_orders[$idx+1]) && ($order->worker_id == @self::$open_orders[$idx+1]->worker_id) ? self::$open_orders[$idx+1]['location']['lat'].",".self::$open_orders[$idx+1]['location']['lon'] : $lat.",".$lng);

//            $complete_times_by_worker[$order->worker_id]['q'][] = $current_location .' --> '. $next_location;

            if($current_location && $next_location && false) {

                $response = \GoogleMaps::load('directions')
                    ->setParam([
                        'origin'=>$current_location,
                        'destination'=>$next_location,
                    ])
                    ->get();
                $json_resp = json_decode($response);
                if($json_resp->status == "OK") {
                    $travel_time = round($json_resp->routes[0]->legs[0]->duration->value/60, 0);
                }

            } else {
                $travel_time = static::$travel_time;
            }

            $complete_times_by_worker[$order->worker_id]['q'][] = $travel_time;

        }

        foreach($complete_times_by_worker as $worker_id=>$q) {

            $complete_times_by_worker[$worker_id]['eta'] = array_sum($q['q']);
        }
dd($complete_times_by_worker);
        //soonest available
        foreach($complete_times_by_worker as $worker_id=>$times) {
            if (empty($soonest_available)) $soonest_available = $times['eta'];
            else if($times['eta'] < $soonest_available) $soonest_available = $times['eta'];
        }

        return $soonest_available;

//        $completion_times=[];
//
//        foreach(self::$open_orders as $order) {
//
//            $service_time = $order->service->time;
//
//            $mins_elapsed = $order->{$order->status."_at"}->diffInMinutes();
//
//            if($order->status == "start") {
////                print "should be done: ".max(0, $service_time - $mins_elapsed)."<br/>";
//                $complete_time = max(10, ($service_time - $mins_elapsed));
//            } else {
//                if($order->eta - $mins_elapsed < 0) {
//                    $complete_time = self::$travel_time + $service_time;
//                } else {
//                    $complete_time = max(10, ($order->eta - $mins_elapsed)) + $service_time;
//                }
////                print "should be done: ".$complete_time."<br/>";
//
//            }
//
//            $completion_times[] = $complete_time;
//
//        }
//
//        sort($completion_times);
//
//        $order_index = max(0, count($completion_times) - $total_active_workers->count());
//
//        try {
//            $eta = $completion_times[$order_index] + self::$travel_time;
//
////            mail('dan@formula23.com', 'etas', $lead_time."\n\n index: $order_index \n\n".print_r($completion_times, 1)." \n\n ETA: $eta");
//
//            return $eta;
//        } catch (\Exception $e) {
//            \Bugsnag::notifyException($e);
//            return self::$travel_time;
//        }

    }

    /**
     * @param $leadtime
     * @return string
     */
    public static function formatConfirmEta($leadtime) {

        if($leadtime < 60) {
            return $leadtime." minutes";
        }

        $hrs = (int)floor($leadtime/60);
        $mins = (int)($leadtime % 60);

        return $hrs." ".str_plural("hour", $hrs)." ".$mins." ".str_plural("min", $mins);
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
        if( ! self::open()) return 0;
//        if(env('APP_DEV')) return 1000;
        $close_time = Carbon::createFromTime(\Config::get('squeegy.operating_hours.close'), env('OPERATING_MIN_CLOSE') ,0);
        return $close_time->diffInMinutes();
    }

    public static function setTravelTime($workers = 1)
    {
        $now = Carbon::now();
        if($now->hour >= 16 && !in_array($now->dayOfWeek, [6,0])) self::$travel_time = 45;

        self::$travel_time = max(25, self::$travel_time - (5 * $workers));
        return;
    }

}