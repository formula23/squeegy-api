<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = ['notification_id','user_id','order_id','message','delivery_method'];

    public function notification()
    {
        return $this->belongsTo('App\Notification');
    }
    
    public function user()
    {
        return $this->belongsTo('App\User');
    }
    
    public function order()
    {
        return $this->belongsTo('App\Order');
    }
}
