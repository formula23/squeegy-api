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
    public $now;
    public $open_hr;
    public $close_hr;
//    public $available=[];
    public $lead_hrs=4;
    public $days_out=7;
    public $time_slot_interval=1;
    public $current_schedule;
    public $postal_code;
    public $day_format;

    public function __construct($postal_code=null)
    {
        if($postal_code) $this->postal_code = $postal_code;

        $this->current_schedule();

        $this->now = Carbon::now();

        $this->open_hr = Config::get('squeegy.operating_hours.open');
        $this->close_hr = Config::get('squeegy.operating_hours.close');

        $this->day_format = (Request::header('X-Device') == "Android" ? 'D, M d' : 'l, F d' );

//		$this->current_day = 8;
//		$this->now = Carbon::create(2016,01,$this->current_day,0,0,0);

    }

    public function availability($partner_id=null)
    {

        if($partner_id) {
            return $this->partner_days($partner_id);
        }

        $idx=0;
        for($i=0; $i<=$this->days_out; $i++)
        {
            $this->now->addDay((!$i?0:1));

            if(empty($container)) $container=[];

            if($this->now->format('Y-m-d') == '2016-03-27') { //easter
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
//				if($this->now->day == $this->current_day) {

                    if($this->now->hour >= $this->close) {
//						print "after close\n";
                        continue(2);
                    }

                    if($this->now->hour >= 0 && $this->now->hour < Config::get('squeegy.operating_hours.open') && $this->open < Config::get('squeegy.operating_hours.open')+$this->lead_hrs) {
//						print "before open\n";
                        continue;
                    }

                    if($this->open < $this->now->hour+$this->lead_hrs) {
//						print "cont\n";
                        continue;
                    }
                    if($this->now->hour+$this->lead_hrs >= $this->close) {
//						print "cont\n";
                        continue(2);
                    }
                }

                //will not block out windows at this time.
                $key = $this->now->format('m/d/Y ').$start->format('H');

                $is_available = (!empty($this->current_schedule[$key]) && $this->current_schedule[$key] >= $this->cap($start->hour)?false:true);
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

    protected function current_schedule()
    {
        $this->current_schedule = Order::current_scheduled_orders();
    }

    protected function partner_days($partner_id)
    {
        $partner = Partner::find($partner_id);

        $container=[];
//        $cur_hr = $this->now->hour;
//        $this->now = Carbon::create(2016,5,12,18,1,0);
//        Log::info("**********************************");
//        Log::info($this->now);
        $cur_hr = $this->now->hour;
//        $cur_hr = 17;
//        Log::info('cur hr:'.$cur_hr);
//        Log::info('day of week:...'.$this->now->dayOfWeek);

        try
        {
            //get array of available days in sequential order.
            $day_sort=[];
            $day_sort_time=[];
            $days = $partner->days()->orderBy('next_date')->get();

            foreach($days as $idx=>$day) {

                $container[$idx]['day'] = $day->next_date->format($this->day_format);
                $container[$idx]['time_slots'][] = implode(" - ", [$day->time_start, $day->time_end]);

//                $day_sort[] = $day->day_of_week;
//                $day_sort_time[$day->day_of_week] = $day->time_end;
            }
dd($container);
//            Log::info('day sort time:');
//            Log::info($day_sort);
//            Log::info($day_sort_time);

            $days_array = $days->toArray();

            //reorder days
//            Log::info($cur_hr);

            //only care about time of day if day of week exists in offered days
            if( ! empty($day_sort_time[$this->now->dayOfWeek]) && $cur_hr < Carbon::parse($day_sort_time[$this->now->dayOfWeek])->hour ) {
                $day_iterator = $this->now->dayOfWeek;
            } else {
                if($this->now->dayOfWeek < 6) {
                    $day_iterator = $this->now->dayOfWeek + 1;
                } else {
                    $day_iterator = 0;
                }
            }

//            $day_iterator=( $cur_hr < (@(int)$day_sort_time[$this->now->dayOfWeek] + 12) ? $this->now->dayOfWeek : ($this->now->dayOfWeek < 6 ? $this->now->dayOfWeek + 1 : 0 ) );

//            Log::info('start day iterator:'.$day_iterator);
//            dd($day_iterator);
            do {
//                Log::info('day iterator:'.$day_iterator);
                $position = array_search($day_iterator, $day_sort);
//                Log::info('position '.$position);
//                Log::info( (@(int)$day_sort_time[$this->now->dayOfWeek] + 12) );
//                Log::info('close time');
//                Log::info(@(int)$day_sort_time[$this->now->dayOfWeek]);

                if($position !== false)
                {
                    $first_part = array_splice($days_array, $position);
                    $days_array = array_merge($first_part, $days_array);
                    break;
                }
                $day_iterator++;

            } while($day_iterator <= 6);

//            Log::info('days array:');
//            Log::info($days_array);

            foreach($days_array as $idx=>$day) {

                if($this->now->dayOfWeek == $day['day_of_week'] && $cur_hr < Carbon::parse($day['time_end'])->hour) {
//                    Log::info('same day within time');
                    $day_display = $this->now;
                } else {
                    if ($this->now->dayOfWeek < $day['day_of_week']) {
//                        Log::info($this->now->dayOfWeek);
//                        Log::info($day['day_of_week']);
//                        Log::info('now < day');
                        if($this->now->dayOfWeek===0) {
//                            Log::info('next '.$day['day']);
//                            $n = $this->now;
//                            $day_display = $n->addDay($day['day_of_week']);
                            $day_display = Carbon::now()->addDay($day['day_of_week']);

                        } else {
//                            Log::info($day['day']);
//                            $n = $this->now;
//                            $day_display = $n->addDay($day['day_of_week'] - $this->now->dayOfWeek);
                            $day_display = Carbon::now()->addDay($day['day_of_week'] - $this->now->dayOfWeek);
                        }

                    } else {
//                        $n = $this->now;
//                        $day_display = $n->next($day['day_of_week']);
                        $day_display = Carbon::now()->next($day['day_of_week']);
                    }
                }

//                Log::info($day_display);
//                Log::info($day);
//                Log::info($day_display->format($this->day_format));
                if($day_display->isToday() && ($cur_hr >= Carbon::parse($day['time_start'])->hour)) {
                    $day['time_start'] = $day_display->addHour(1)->format('g:00a');
                    if($cur_hr+1 == Carbon::parse($day['time_end'])->hour) {
                        $day['time_end'] = $day_display->addHour(1)->format('g:00a');
                    }
//                    Log::info($cur_hr);
//                    Log::info(Carbon::parse($day['time_start'])->hour);
                }

                $container[$idx]['day'] = $day_display->format($this->day_format);
                $container[$idx]['time_slots'][] = implode(" - ", [$day['time_start'], $day['time_end']]);

            }
        } catch (\Exception $e) {
            Log::info($e);
            \Bugsnag::notifyException($e);
        }

        return $container;
    }

    private function cap($hr)
    {
        return ($hr==9 ? 2 : 3 );
    }

}