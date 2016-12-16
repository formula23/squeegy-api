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

            if($this->now->format('Y-m-d') == '2016-11-24') { //thanksgiving
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

            if($day->gte(Carbon::create(2016,12,17,16,45,00))) {
                continue;
            }

            $container[$idx] = ['day'=>$day];

//            $this->open = $this->open_hr;
            $this->open = 9;
            $this->close = $this->close_hr;
            $windows=[];

            for($this->open; $this->open<=$this->close-1; $this->open++) {

                $start = new Carbon($this->now->format("m/d/y $this->open:00"));

                if($this->now->gte(Carbon::create(2016,12,17,16,45,00))) {
                    continue(2);
                }

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
        $container['next_day']='';
        $container['available_days']=[];

//        $cur_hr = $this->now->hour;
//        $this->now = Carbon::create(2016,5,12,18,1,0);
        Log::info("***************** PARTNER DAYS *********************");
//        Log::info($this->now);
        $cur_hr = $this->now->hour;
//        $cur_hr = 17;
//        Log::info('cur hr:'.$cur_hr);
//        Log::info('day of week:...'.$this->now->dayOfWeek);
        Log::info('Current Schedule:');
        Log::info($this->current_schedule);

        try
        {
            //get array of available days in sequential order.
            $days_list=[];
            $days = $this->partner->days()->orderBy('open')->get();

            foreach($days as $idx=>$day) {

                \Log::info('accepting orders:');
                \Log::info($day->open);
                \Log::info($day);

                if( ! $day->accepting_orders || $day->close->isPast()) {
                    continue;
                }
                Log::info($day);

                $start_time = $day->open;
                $end_time = $day->close;
                $num_hrs = $start_time->diffInHours($end_time);

                $day_formatted = $day->open->format('D, M d');

                if(in_array($day_formatted, $days_list)) {
                    $idx = array_search($day_formatted, $days_list);
                }

                $container['available_days'][$idx]['day'] = $day_formatted;
                $days_list[$idx]=$day_formatted;

                if($day->time_slot_frequency) {

                    for($h=0;$h<$num_hrs;$h++) {

                        if($start_time->gt($end_time)) {
                            Log::info('continue');
                            continue;
                        }

                        if($day->time_slot_cap && @(int)$this->current_schedule[$start_time->format('m/d/Y')][$start_time->format('H')] >= $day->time_slot_cap) {
                            $start_time->addHours($day->time_slot_frequency);
                            if($start_time->gte($end_time)) {
                                unset($container['available_days'][$idx]);
                                continue(2);
                            } else {
                                continue;
                            }
                        }
                        $strt = $start_time->format('g:ia');
                        $end = $start_time->addHours($day->time_slot_frequency);
                        if($end->isPast()) {continue;}

                        if($end->gte($end_time)) {
                            $end = $end_time;
                            if( ! $start_time->diffInHours($end)) {
                                $container['available_days'][$idx]['time_slots'][] = implode(" - ", [$strt, $end->format('g:ia')]);
                                continue(2);
                            }
                        }

                        $container['available_days'][$idx]['time_slots'][] = implode(" - ", [$strt, $end->format('g:ia')]);
                    }

                } else {
                    if($day->order_cap && @(int)$this->current_schedule[$start_time->format('m/d/Y')][$start_time->format('H')] >= $day->order_cap) {
                        unset($container['available_days'][$idx]);
                        continue;
                    }

                    Log::info($day);

                    $container['available_days'][$idx]['time_slots'][] = implode(" - ", [$day->open->format('g:ia'), $day->close->format('g:ia')]);
                }

            }
\Log::info($container);
            if( ! count($container['available_days'])) {
                $container['next_day'] = $this->partner->upcoming_date();
            }

            $container['available_days'] = array_values($container['available_days']);

            \Log::info('********** container ***********');
            \Log::info($container);
//            dd('done');
            return $container;
            
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