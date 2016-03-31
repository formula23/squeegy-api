<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OrderSchedule extends Model {

	protected $fillable = ['order_id', 'window_open', 'window_close', 'type'];

    protected $dates = ['window_open', 'window_close'];

    public function order()
    {
        return $this->belongsTo('App\Order');
    }

    public function display_time()
    {
        if( ! $this->window_open) return null;
        if($this->type=='one-time') {
            return $this->window_open->format('g')."-".$this->window_close->format('ga');
        } else {
            return $this->window_open->format('l ga');
        }
    }

}
