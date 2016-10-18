<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 1/24/16
 * Time: 11:50
 */

namespace App\Squeegy;

use App\Order;
use App\OrderSchedule;
use App\Partner;
use Aws\CloudTrail\LogFileIterator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class Schedule
{
    public $partner;
    public $now;
    public $open_hr;
    public $close_hr;
//    public $available=[];
    public $lead_hrs=0;
    public $days_out=7;
    public $time_slot_interval=1;
    public $current_schedule;
    public $postal_code;
    public $day_format;
    public $availability;

    public function __construct($postal_code=null, $partner_id=0)
    {
        if($postal_code) $this->postal_code = $postal_code;
        if($partner_id) {
            $this->partner = Partner::find($partner_id);
        }

        $this->current_schedule = Order::current_scheduled_orders($partner_id);

        $this->set_lead_time();

        $this->now = Carbon::now();

        $this->open_hr = Config::get('squeegy.operating_hours.open');
        $this->close_hr = Config::get('squeegy.operating_hours.close');

        $this->day_format = (Request::header('X-Device') == "Android" ? 'D, M d' : 'l, F d' );

//		$this->current_day = 8;
//		$this->now = Carbon::create(2016,01,$this->current_day,0,0,0);

    }

    protected function set_lead_time()
    {
        $this->availability = Orders::availability(\Request::input('lat'), \Request::input('lng'));

        if($this->availability["schedule"] && ! $this->availability["accept"]) return;

        $this->lead_hrs = (int)round(@$this->availability["actual_time"]/60);
    }

    public function availability()
    {
        if($this->partner) {
            return $this->partner_days();
        }

        \Log::info('on-demand current schedule:');
        \Log::info($this->current_schedule);

        $idx=0;
        for($i=0; $i<=$this->days_out; $i++)
        {
            $this->now->addDay((!$i?0:1));

            if(empty($container)) $container=[];

            if($this->now->format('Y-m-d') == '2016-09-05') { //labor day
                $this->days_out+=1;
                continue;
            }

//            if($this->now->isSunday()) {
//                $this->days_out+=1;
//                continue;
//            }

//            if($this->now->isToday()) {
//                $day = "Today (".$this->now->format('m/d').")";
//            } elseif($this->now->isTomorrow()) {
//                $day = "Tomorrow (".$this->now->format('m/d').")";
//            } else {
                $day = $this->now->format($this->day_format);
//            }

            $container[$idx] = ['day'=>$day];

//            $this->open = $this->open_hr;
            $this->open = 9;
            $this->close = $this->close_hr;
            $windows=[];

            for($this->open; $this->open<=$this->close-1; $this->open++) {

                $start = new Carbon($this->now->format("m/d/y $this->open:00"));

                if($this->now->isToday()) {

                    if($this->now->hour >= $this->close) { //squeegy closed
                        continue(2);
                    }

                    /** First time slot is 9am no need to give lead time after midnight...System will auto-assign orders... */
//                    if($this->now->hour >= 0 &&
//                        $this->now->hour < Config::get('squeegy.operating_hours.open') &&
//                        $this->open < Config::get('squeegy.operating_hours.open')+$this->lead_hrs) {
//                        continue;
//                    }
                    if( $this->availability["schedule"] && $this->availability["open"] ) { ///no washers availble and squeegy is open.
                        continue(2);
                    }

                    if( $this->open < ($this->now->hour + $this->lead_hrs) ) {
                        continue;
                    }

                    if( ($this->now->hour + $this->lead_hrs) >= $this->close ) {
                        continue(2);
                    }
                }

                //will not block out windows at this time.
                $key = $this->now->format('m/d/Y');
                $key2 = $start->format('H');

                $is_available = ( ! empty($this->current_schedule[$key][$key2]) && ($this->current_schedule[$key][$key2] >= $this->cap($start->hour) ) ? false : true );
                if( ! $is_available) continue;

                $windows[] = $start->format('g:00a')." - ".$start->addHours($this->time_slot_interval)->format('g:00a');
            }

            if(count($windows)) {
                $container[$idx]['time_slots'] = $windows;
                $idx+=1;
            }
        }

        return $container;
    }

//    protected function current_schedule()
//    {
//        $this->current_schedule = Order::current_scheduled_orders($this->partner_id);
////        Log::info($this->current_schedule);
//    }

    protected function partner_days()
    {
//        $this->current_schedule = $partner->current_scheduled_orders();

        $container=[];
//        $cur_hr = $this->now->hour;
//        $this->now = Carbon::create(2016,5,12,18,1,0);
        Log::info("**********************************");
//        Log::info($this->now);
        $cur_hr = $this->now->hour;
//        $cur_hr = 17;
//        Log::info('cur hr:'.$cur_hr);
//        Log::info('day of week:...'.$this->now->dayOfWeek);
        Log::info($this->current_schedule);

        try
        {
            //get array of available days in sequential order.
            $day_sort=[];
            $day_sort_time=[];
            $days = $this->partner->days()->orderBy('open')->get();

            foreach($days as $idx=>$day) {

//                if($day->accept_order($day->open) === -1) { //daily cap has been reached...
//                    continue;
//                }
//
                $start_time = $day->open;
                $end_time = $day->close;
                $num_hrs = $start_time->diffInHours($end_time);
//
//                if($day->open->isPast()) {
//                    $container[$idx]['day'] = 'Not Available';
//                    $container[$idx]['time_slots'][] = 'Not Available';
//                    continue;
//                }

                $container[$idx]['day'] = $day->open->format($this->day_format);

                if(in_array($this->partner->id, [5])) {

                    for($h=0;$h<$num_hrs;$h++) {

                        if($start_time->gt($end_time)) {
                            continue;
                        }

                        if(@(int)$this->current_schedule[$start_time->format('m/d/Y H')] >= 2) { ///only allow 2 orders per slot
                            $start_time->addHours(1);
                            continue;
                        }
                        $strt = $start_time->format('g:ia');
                        $end = $start_time->addHours(1);
                        if($end->isPast()) {continue;}

                        $container[$idx]['time_slots'][] = implode(" - ", [$strt, $end->format('g:ia')]);
                    }

                } elseif(in_array($this->partner->id, [14,16,22])) {

                    $display_timeslot=true;

                    if(@(int)$this->current_schedule[$start_time->format('m/d/Y')]['08'] < $day->time_slot_cap) {
                        if($start_time->isFuture() || ($start_time->isToday() && Carbon::now()->hour < 10) || $display_timeslot) {
                            $container[$idx]['time_slots'][] = '8:00am - 10:00am';
                            $display_timeslot=false;
                        }
                    }
                    if(@(int)$this->current_schedule[$start_time->format('m/d/Y')]['10'] < $day->time_slot_cap) {
                        if($start_time->isFuture() || ($start_time->isToday() && Carbon::now()->hour > 10 && Carbon::now()->hour < 12) || $display_timeslot) {
                            $container[$idx]['time_slots'][] = '10:00am - 12:00pm';
                            $display_timeslot=false;
                        }
                    }
                    if(@(int)$this->current_schedule[$start_time->format('m/d/Y')]['12'] < $day->time_slot_cap) {
                        if($start_time->isFuture() || ($start_time->isToday() && Carbon::now()->hour > 12 && Carbon::now()->hour < 14) || $display_timeslot) {
                            $container[$idx]['time_slots'][] = '12:00pm - 2:00pm';
                            $display_timeslot=false;
                        }
                    }
                    if(@(int)$this->current_schedule[$start_time->format('m/d/Y')]['14'] < $day->time_slot_cap && Carbon::now()->hour < 16) {
                        if($start_time->isFuture() || ($start_time->isToday() && Carbon::now()->hour > 14 && Carbon::now()->hour < 16) || $display_timeslot) {
                            $container[$idx]['time_slots'][] = '2:00pm - 4:00pm';
                        }
                    }
                    
                    if(@ ! count($container[$idx]['time_slots'])) {
                        $container[$idx]['time_slots'][] = 'Unavailable';
                    }

                } elseif(in_array($this->partner->id, [26])) {
                    if(@(int)$this->current_schedule[$start_time->format('m/d/Y')]['08'] < $day->time_slot_cap) {
                        $container[$idx]['time_slots'][] = '8:00am - 1:00pm';
                    }

                    if(@(int)$this->current_schedule[$start_time->format('m/d/Y')]['13'] < $day->time_slot_cap) {
                        $container[$idx]['time_slots'][] = '1:00pm - 5:00pm';
                    }

                    if(@ ! count($container[$idx]['time_slots'])) {
                        $container[$idx]['time_slots'][] = 'Unavailable';
                    }

                } else {
                    $container[$idx]['time_slots'][] = implode(" - ", [$day->open->format('g:ia'), $day->close->format('g:ia')]);
                }

            }

            return array_values($container);
            
        } catch (\Exception $e) {
            Log::info($e);
            \Bugsnag::notifyException($e);
        }

        return $container;
    }

    private function cap($hr)
    {
        return ($hr==9 ? 1 : 1 );
    }

}