<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 1/24/16
 * Time: 11:50
 */

namespace App\Squeegy;


use App\OrderSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class Schedule
{
    public $now;
    public $available=[];
    public $lead_hrs=3;
    public $days_out=5;
    public $current_schedule;

    public function __construct()
    {
        $this->current_schedule();

        $this->now = Carbon::now();

//		$current_day = 7;
//		$this->now = Carbon::create(2016,01,$current_day,9,0,0);

    }

    public function availability()
    {
        for($i=0; $i<=$this->days_out; $i++) {

            $this->now->addDay((!$i?0:1));

            if($this->now->isSunday()) {
                $this->days_out+=1;
                continue;
            }

            $open=Config::get('squeegy.operating_hours.open');
            $close = Config::get('squeegy.operating_hours.close');

            for($open; $open<=$close-1; $open++) {
//print $open;
                $start = new Carbon($this->now->format("m/d/y $open:00"));

                if($this->now->isToday()) {
//				if($this->now->day == $current_day) {

                    if($this->now->hour >= $close) {
//						print "after close\n";
                        continue(2);
                    }

                    if($this->now->hour >= 0 && $this->now->hour < Config::get('squeegy.operating_hours.open') && $open < Config::get('squeegy.operating_hours.open')+$this->lead_hrs) {
//						print "before open\n";
                        continue;
                    }

                    if($open < $this->now->hour+$this->lead_hrs) {
//						print "cont\n";
                        continue;
                    }
                    if($this->now->hour+$this->lead_hrs >= $close) {
//						print "cont\n";
                        continue(2);
                    }
                }

                $key = $this->now->format('m/d/Y ').$start->format('H');

                $is_available = (!empty($this->current_schedule[$key]) && $this->current_schedule[$key]>=3?false:true);

                $this->available[$this->now->format('l, F d')][] = ["window"=>$start->format('g:00a')." - ".$start->addHours(1)->format('g:00a'), "available"=>$is_available];
            }
        }

        return $this->available;
    }

    protected function current_schedule()
    {
        $this->current_schedule = OrderSchedule::current_scheduled_orders();
    }

}