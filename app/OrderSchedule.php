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
}
