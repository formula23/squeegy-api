<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
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
