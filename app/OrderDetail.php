<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model {

    protected $fillable = ['order_id', 'name', 'amount'];

    public function order()
    {
        return $this->belongsTo('App\Order');
    }

}
