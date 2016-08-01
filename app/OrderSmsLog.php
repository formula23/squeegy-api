<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderSmsLog extends Model
{
    protected $fillable = ['order_id','from','to','message'];
    
    public function order()
    {
        return $this->belongsTo('App\Order');
    }
    
    public function from()
    {
        return $this->belongsTo('App\User', 'from', 'id');
    }

    public function to()
    {
        return $this->belongsTo('App\User', 'to', 'id');
    }
    
}
