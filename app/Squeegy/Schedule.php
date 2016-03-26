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
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;

class Schedule
{
    public $now;
    public $open_hr;
    public $close_hr;
//    public $available=[];
    public $lead_hrs=4;
    public $days_out=6;
    public $time_slot_interval=1;
    public $current_schedule;
    public $postal_code;

    public function __construct($postal_code=null)
    {
        if($postal_code) $this->postal_code = $postal_code;

        $this->current_schedule();

        $this->now = Carbon::now();

        $this->open_hr = Config::get('squeegy.operating_hours.open');
        $this->close_hr = Config::get('squeegy.operating_hours.close');

//		$this->current_day = 8;
//		$this->now = Carbon::create(2016,01,$this->current_day,0,0,0);

    }

    public function availability()
    {
        $day_format = (Request::header('X-Device') == "Android" ? 'D, M d' : 'l, F d' );

        if($this->postal_code == 90015) { //downtown pilot
            $day = ( $this->now->dayOfWeek == 3 && $this->now->hour < 18 ? $this->now->format($day_format) : $this->now->next(3)->format($day_format) );
            $container=[];
            $container[0]['day'] = $day;
            $container[0]['time_slots'][] = "10:00am - 6:00pm";
            return $container;
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
                $day = $this->now->format($day_format);
//            }

            $container[$idx] = ['day'=>$day];

            $this->open = $this->open_hr;
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
//                $key = $this->now->format('m/d/Y ').$start->format('H');
//                $is_available = (!empty($this->current_schedule[$key]) && $this->current_schedule[$key]>=3?false:true);
//                if( ! $is_available) continue;

//                if(Request::header('X-Device') == "Android") {
//                    $windows[] = $start->format('g')."-".$start->addHours($this->time_slot_interval)->format('ga');
//                } else {
                    $windows[] = $start->format('g:00a')." - ".$start->addHours($this->time_slot_interval)->format('g:00a');
//                }

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

}