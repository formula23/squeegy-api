<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderSchedule extends Model {

	protected $fillable = ['order_id', 'window_open', 'window_close'];

    public function order()
    {
        return $this->belongsTo('App\Order');
    }

}
