<?php namespace App\Squeegy;
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/15/15
 * Time: 18:18
 */

use App\Order;
use App\Region;
use App\User;
use App\Zone;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

    protected static $travel_time = 30;
    protected static $travel_time_buffer = 5;
    protected static $travel_time_buffer_pct = 1.2;
    protected static $open_orders;
    protected static $bypass_time = 15;
    protected static $mph = 13;
    protected static $last_job = null;
    protected static $final_location = null;
    protected static $current_location = null;
    protected static $holiday=null;

    public static $lat=null;
    public static $lng=null;
    public static $city=null;
    public static $state=null;
    public static $postal_code=null;

    /**
     * @return bool
     */
    public static function open()
    {
        if(is_internal()) return true;

        if( ! env('OPERATING_OPEN') || env('MAINTENANCE')) return false;

        $now = Carbon::now();
//        $now = Carbon::create(2015,12,25,13,46,0);

        if($now->dayOfWeek == 0) return false;

        $open_time = Carbon::createFromTime(config('squeegy.operating_hours.open'), 0, 0);
        $close_time = Carbon::createFromTime(config('squeegy.operating_hours.close'), env('OPERATING_MIN_CLOSE', 0), 0);

        //holidays
        // thanksgiving - closed 11/26
        if($now > Carbon::create(2015,11,25,16,45) && $now < Carbon::create(2015,11,26,23,59,59)) {
            self::$holiday = 'thanksgiving';
            return false;
        }

        if($now > Carbon::create(2015,12,24,16,30) && $now < Carbon::create(2015,12,25,23,59,59)) {
            self::$holiday = "xmas";
            return false;
        }

        if($now > Carbon::create(2015,12,31,16,30) && $now < Carbon::create(2016,01,01,23,59,59)) {
            self::$holiday = "newyear";
            return false;
        }

//        $open_time = Carbon::create(2015,12,25,8,0,0);
//        $close_time = Carbon::create(2015,12,25,16,30,0);

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
    public static function availability($lat=null, $lng=null)
    {
        $data = ['accept'=>self::open(), 'description'=>'', 'code'=>'', 'time'=>0, 'time_label'=>'', 'service_area' => config('squeegy.service_area')];

        self::geocode(self::get_location($lat, $lng));
        self::$lat = $lat;
        self::$lng = $lng;

        if( ! self::open()) {

            if(env('MAINTENANCE')) {
                $data['accept'] = 0;
                $data['description'] = "Squeegy is currently closed for scheduled maintenance.";
                $data['code'] = "maintenance";
                return $data;
            }

            if(self::$holiday != null) {
                switch(self::$holiday) {
                    case "thanksgiving":
                        $data['description'] = "Happy Thanksgiving!\nWe'll be back Friday, 9am - 4:45pm";
                        break;
                    case "xmas":
                        $data['description'] = "Merry Christmas from Squeegy!\nWe'll be back Saturday 26th, 8am - 4:30pm";
                        break;
                    case "newyear":
                        $data['description'] = "Happy New Year!\nSqueegy will return Saturday 2nd, 8am - 4:30pm";
                        break;
                }
                $data['accept'] = 0;
                $data['code'] = "holiday";
                return $data;
            }

//            $open_time = Carbon::create(2015,11,25,9,0,0);
//            $close_time = Carbon::create(2015,11,25,16,45,0);

            $now = Carbon::now();
//            $now = Carbon::create(2015,11,25,17,46,0);

            $day_of_week = $now->dayOfWeek;
            $curr_hr = $now->hour;

            $next_day = ($curr_hr >= env('OPERATING_HR_CLOSE') && $curr_hr <= 23 || !env('OPERATING_WKND') ? $now->addDay()->format('l') : $now->format('l') );
            
            $next_day_is_monday = ($curr_hr >= env('OPERATING_HR_CLOSE') || ($curr_hr = env('OPERATING_HR_CLOSE') && $now->minute >= env('OPERATING_MIN_CLOSE')));
                
            if($day_of_week == 6 && $next_day_is_monday || $day_of_week == 0) {
                $next_day = "Monday";
            }

            $data['description'] = trans('messages.service.closed', ['next_day' => $next_day, 'close_mins'=>(env('OPERATING_MIN_CLOSE')=='00' ? 'pm' : ':'.env('OPERATING_MIN_CLOSE').'pm' )]);
            $data['code'] = "closed";
            return $data;
        }

        $eta = self::getLeadTime($lat, $lng);

        $data['zip_code'] = self::$postal_code;

        if ( ! empty($eta['error_msg'])) {
            $data['accept'] = 0;
            $data['description'] = $eta['error_msg'];
            $data['code'] = $eta['error_code'];
            return $data;
        }

        $data['lead_time'] = $eta['time'];
        $data['worker_id'] = $eta['worker_id'];


        if(! is_internal() && self::open() && $data['lead_time'] > (self::remainingBusinessTime() + self::CLOSING_BUFFER)) {
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

    public static function getLeadTimeByOrder(Order $order)
    {
        return self::getLeadTime($order->location['lat'], $order->location['lon'], $order);
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
    public static function getLeadTime($lat, $lng, Order $order=null)
    {
        //geo-code customer request location lat-long
        //used to get correct workers

        $request_loc_pair = self::get_location($lat, $lng);

        self::geocode($request_loc_pair);

        $regions = Region::where('postal_code', self::$postal_code)->get();
        if( ! $regions->count()) {
            return ['error_msg'=>trans('messages.service.outside_area'), 'error_code'=>'outside_area'];
        }

        $active_workers_qry = User::activeWashers(self::$postal_code);

        $active_workers = $active_workers_qry->get();

        if( ! $active_workers->count()) return ['error_msg'=>trans('messages.service.not_available'), 'error_code'=>'not_available'];

        $complete_times_by_worker=[];
        $complete_times_by_worker_debug=[];

        $bypass_job = [];

        if(env('ETA_LOGGING')) {
            Log::info('****************************** ETA LOGGING *****************************');
            $start = microtime(true);
            Log::info('Start: '.$start);
        }


        foreach($active_workers as $active_worker) {

            $worker_origin = self::get_workers_location($active_worker);

            if($active_worker->jobs->count() < 2) {
                if(self::$final_location && self::$final_location->status != 'done') {
                    $final_location = self::$final_location->location['lat'].",".self::$final_location->location['lon'];
                } else {
                    $final_location = $worker_origin;
                }

                $byp_time = self::getTravelTime($final_location, $request_loc_pair);
                $complete_times_by_worker_debug[$active_worker->id]['q']['bypass--'] = $final_location."-->".$request_loc_pair." (trvl time:$byp_time)";

                if($byp_time <= self::$bypass_time) {
                    $bypass_job[$active_worker->id] = $byp_time;
                }
            }

            if( ! count($active_worker->jobs) ) {
                $travel_time = self::getTravelTime($worker_origin, $request_loc_pair);
                $complete_times_by_worker_debug[$active_worker->id]['q']['default_travel--'] = $worker_origin."-->".$request_loc_pair." (trvl time:$travel_time)";
                $complete_times_by_worker[$active_worker->id]['q']['default_travel'] = $travel_time;
                continue;
            }

            foreach($active_worker->jobs as $idx => $job) {

                if($job->status == "start") {
                    $complete_times_by_worker[$active_worker->id]['q']['remaining_start'.$idx] = max(5, $job->service->time - $job->start_at->diffInMinutes());

                } else if($job->status == "enroute") {
                    /* calc remaining travel time for first job
                    * need to see if there have been previous jobs to this one during the day
                     * is worker_origin, default location or location of previous job
                     *
                     * determining elapsed time, to deduct from travel time
                     * if there is a previous job and it was finished after this job was created, elapsed time is calculate using the previous job
                     * if the current job was created after the previous job ended, calculate elapsed time using current job.
                    */
                    if( ! isset($complete_times_by_worker[$active_worker->id])) { //washers first job - calc remaining

                        self::get_last_job($active_worker);

                        $destination = implode(",", [$job->location['lat'], $job->location['lon']]);

//                        $travel_time = self::getTravelTime($worker_origin, $destination, true);

                        $travel_time = self::getRealTravelTime($worker_origin, $destination); //google map directions

//                        if(self::$last_job && ($job->enroute_at < self::$last_job->done_at)) {
//                            $time_elapsed = self::$last_job->done_at->diffInMinutes();
//                            $complete_times_by_worker2[$active_worker->id]['q']['elapse time job'] = self::$last_job->id;
//                        } else {
//                            $time_elapsed = $job->enroute_at->diffInMinutes();
//                            $complete_times_by_worker2[$active_worker->id]['q']['elapse time job'] = $job->id;
//                        }


//                        $complete_times_by_worker[$active_worker->id]['q']['remaining_route'.$idx] = max(5, $travel_time - $time_elapsed);
                        $complete_times_by_worker[$active_worker->id]['q']['remaining_route'.$idx] = $travel_time;
                        $complete_times_by_worker_debug[$active_worker->id]['q']['remaining route time---'.$job->id] = $worker_origin."-->".$destination." (trvl time:$travel_time)";
                    }

                    $complete_times_by_worker[$active_worker->id]['q']['job time'.$idx] = (int)$job->service->time;
                    $complete_times_by_worker_debug[$active_worker->id]['q']['job time'.$job->id] = (int)$job->service->time;
                }

                $current_location = implode(",", [$job->location['lat'], $job->location['lon']]);
                //next location
                $next_job = $active_worker->jobs->get($idx+1);
                $next_location = ( $next_job ? implode(",", [$next_job->location['lat'],$next_job->location['lon']]) : $request_loc_pair ); //else location of requesting job

                $travel_time = self::getTravelTime($current_location, $next_location);


                $complete_times_by_worker[$active_worker->id]['q']['travel time'.$idx] = $travel_time;
                $complete_times_by_worker_debug[$active_worker->id]['q']['travel time---'.$job->id] = $current_location."-->".$next_location." (trvl time: $travel_time)";
            }

        }

        foreach($complete_times_by_worker as $worker_id=>$q) {
            $complete_times_by_worker[$worker_id]['eta'] = array_sum($q['q']);
        }

        $next_available = [];
        $tmp_bypass_job = [];

        if(count($bypass_job)) {
            foreach($bypass_job as $worker_id=>$travel_tm) {
                @$tmp_bypass_job[$worker_id] = $complete_times_by_worker[$worker_id]['eta'];
            }
            asort($tmp_bypass_job);
            $worker_id = key($tmp_bypass_job);
            $next_available['time'] = $complete_times_by_worker[$worker_id]['eta'];
            $next_available['worker_id'] = $worker_id;

        } else {
            foreach($complete_times_by_worker as $worker_id=>$times) {
                if (empty($next_available)) {
                    $next_available['time'] = $times['eta'];
                    $next_available['worker_id'] = $worker_id;
                }
                else if($times['eta'] < $next_available['time']) {
                    $next_available['time'] = $times['eta'];
                    $next_available['worker_id'] = $worker_id;
                }
            }
        }

        if(env('ETA_LOGGING')) {
            Log::info("Requested location: $request_loc_pair");
            Log::info('Complete times by worker');
            Log::info(print_r($complete_times_by_worker,1));

            Log::info('Complete times by worker DEBUG');
            Log::info(print_r($complete_times_by_worker_debug,1));

            Log::info('Bypass jobs');
            Log::info(print_r($bypass_job,1));

            Log::info('bypass job actual eta');
            Log::info(print_r($tmp_bypass_job,1));

            Log::info('next available');
            Log::info(print_r($next_available,1));

            $end = microtime(true);
            Log::info('Execution time: '.$end - $start);

            Log::info('****************************** /ETA LOGGING *****************************');
        }

        return $next_available;
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

    public static function getTravelTime($origin, $destination, $current_route=false)
    {

        $miles = self::get_distance($origin, $destination);

        switch(true) {
            case ($miles < 3):
                self::$mph = 16;
                break;
            case ($miles >= 3 && $miles < 7):
                self::$mph = 18;
                break;
            case ($miles >= 7 && $miles < 10):
                self::$mph = 20;
                break;
            case ($miles >= 10 && $miles < 13):
                self::$mph = 25;
                break;
            case ($miles >= 13):
                self::$mph = 30;
                break;
        }

        $actual_time = round(($miles / self::$mph) * 60);
        if($current_route) {
            return max(5, $actual_time);
        } else {
            $travel_time = max(8, $actual_time);
            return round($travel_time * self::traffic_buffer($travel_time));
        }

    }

    public static function getRealTravelTime($origin, $destination, $cache_exp=1440)
    {
        try {

            $cache_key = implode(",", [$origin,$destination]);
            if(Cache::has($cache_key)) {
                $travel_time = Cache::get($cache_key);
            } else {
                $response = \GoogleMaps::load('directions')
                    ->setParam([
                        'origin'=>$origin,
                        'destination'=>$destination,
                    ])
                    ->get();
                $json_resp = json_decode($response);
                if($json_resp->status == "OK") {
                    $travel_time = round($json_resp->routes[0]->legs[0]->duration->value/60, 0);
                    if( ! $cache_exp) {
                        Cache::forever($cache_key, $travel_time);
                    } else {
                        Cache::put($cache_key, $travel_time, $cache_exp);
                    }
                }
            }

        } catch (\Exception $e) {
            \Bugsnag::notifyException($e);
        }

        $travel_time = max(self::$travel_time_buffer, $travel_time);

        return round($travel_time * self::traffic_buffer($travel_time));
    }

    public static function get_distance($pt1, $pt2)
    {
        list($lat1, $lon1) = explode(",", $pt1);
        list($lat2, $lon2) = explode(",", $pt2);

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = round($dist * 60 * 1.1515);

        return ($miles * 1.3); // increase miles by 30% to account for turns a 'real' driving direction
    }

    public static function get_location($lat, $lng)
    {
        return implode(",", [
            'lat'=>round((float)$lat, 2),
            'lng'=>round((float)$lng, 2),
        ]);
    }

    public static function geocode($latlng)
    {
        try {

            if(Cache::has($latlng)) {
                $results = Cache::get($latlng);
                self::parse_add_components($results);
            } else {
                $response = \GoogleMaps::load('geocoding')->setParam (['latlng' => $latlng])->get();
                $json_resp = json_decode($response);
                if($json_resp->status == "OK") {
                    self::parse_add_components($json_resp->results);
                    Cache::forever($latlng, $json_resp->results);
                }
            }
        } catch (\Exception $e) {
            \Bugsnag::notifyException($e);
        }

        return;
    }

    public static function parse_add_components($results)
    {
        $data_cnt=0;

        foreach($results as $result) {

            foreach ($result->address_components as $address_component) {
                if($data_cnt==3) { break(2); }

                if ($address_component->types[0] == "postal_code") {
                    self::$postal_code = $address_component->long_name;
                    $data_cnt++;
                }
                if ($address_component->types[0] == "locality") {
                    self::$city = $address_component->long_name;
                    $data_cnt++;
                }
                if ($address_component->types[0] == "administrative_area_level_1") {
                    self::$state = $address_component->short_name;
                    $data_cnt++;
                }
            }
        }
        return;
    }

    public static function traffic_buffer($travel_time)
    {
        if($travel_time < 20) {
            return 1.5;
        } else {
            return 1.3;
        }
//        if( Carbon::now()->hour >= 16 && false) {
//            return 1.4;
//        } else {
//            return 1.3;
//        }
    }

    public static function get_workers_location(User $worker)
    {
        self::get_final_location($worker);

        if(self::$final_location && self::$final_location->status == 'start') {
            $arr = array_only(self::$final_location->location, ['lat', 'lon']);
            $location = implode(",", [$arr['lat'], $arr['lon']]);
        } else {
            if( ! empty($worker->current_location)) {
                $location = implode(",", array_only($worker->current_location->toArray(), ['latitude', 'longitude']));
            } else if( ! empty($worker->default_location)) {
                $location = implode(",", array_only($worker->default_location->toArray(), ['latitude', 'longitude']));
            } else {
                $location = implode(",", \Config::get('squeegy.worker_default_location'));
            }
        }
        return $location;
    }

    public static function get_final_location(User $worker) {
        self::$final_location = $worker
            ->jobs()
            ->whereIn('status', ['enroute','start','done'])
            ->whereDate('enroute_at', '=', Carbon::today()->toDateString())
            ->orderBy('enroute_at', 'desc')
            ->first();
    }

    public static function get_last_job(User $worker)
    {
        self::$last_job = $worker
            ->jobs()
            ->whereIn('status', ['done'])
            ->whereDate('enroute_at', '=', Carbon::today()->toDateString())
            ->orderBy('enroute_at', 'desc')
            ->first();
    }

}