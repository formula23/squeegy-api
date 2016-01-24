<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OrderSchedule extends Model {

	protected $fillable = ['order_id', 'window_open', 'window_close'];

    protected $dates = ['window_open', 'window_close'];

    public function order()
    {
        return $this->belongsTo('App\Order');
    }

    public static function current_scheduled_orders()
    {
        $existing_schedules = self::whereDate('window_open', '>', Carbon::now())->orderBy('window_open')->get();

        $current_schedule=[];
        foreach($existing_schedules as $existing_schedule) {
            $key = $existing_schedule->window_open->format('m/d/Y H');
            if(empty($current_schedule[$key])) $current_schedule[$key]=0;
            $current_schedule[$key]+=1;
        }

        return $current_schedule;

    }

}
