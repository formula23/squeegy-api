<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model {

    protected $fillable = ['order_id', 'addon_id', 'name', 'amount'];

    public function order()
    {
        return $this->belongsTo('App\Order');
    }

    public function addon()
    {
        return $this->belongsTo('App\Addon');
    }

}
