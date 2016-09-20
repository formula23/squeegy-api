<?php

namespace App;

use App\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PartnerDay extends Model
{
    protected $fillable = ['partner_id','day','day_of_week','next_date','time_start','time_end','order_cut_off_time','frequency','order_cap', 'time_slot_cap', 'accepting_orders',
    'open','close','cutoff'];

    protected $dates = ['next_date','open','close','cutoff'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner()
    {
        return $this->belongsTo('App\Partner');
    }
    
    public function accept_order($requested_date)
    {
//        try {

            if( ! $this->accepting_orders) return -1;

            $current_schedule = Order::current_scheduled_orders($this->partner_id);

            if( $this->order_cap > 0 &&
                isset($current_schedule[$requested_date->format('m/d/Y')]) &&
                array_sum($current_schedule[$requested_date->format('m/d/Y')]) >= $this->order_cap )
            {
                return -1;
            }

            if($this->time_slot_cap > 0 &&
                isset($current_schedule[$requested_date->format('m/d/Y')][$requested_date->format('H')]) &&
                $current_schedule[$requested_date->format('m/d/Y')][$requested_date->format('H')] >= $this->time_slot_cap )
            {
                return -2;
            }

            return 1;
//        } catch (\Exception $e) {
//            \
//            \Bugsnag::notifyException($e);
//        }

        return -1;
    }

    public function next_date_on_site()
    {
        $next_date="";
        switch ($this->frequency) {
            case "weekly":
                $next_date = $this->open->addWeek(1);
                break;
            case "bi-weekly":
                $next_date = $this->open->addWeek(2);
                break;
            case "monthly":
                $next_date = $this->open->addWeek(4);
                break;
        }
        return $next_date;
    }

    public function start_time($format='g:ia')
    {
//        return Carbon::parse($this->time_start)->format($format);
        return $this->open->format($format);
    }

    public function end_time($format='g:ia')
    {
//        return Carbon::parse($this->time_end)->format($format);
        return $this->close->format($format);
    }

}
