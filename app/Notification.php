<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    
    public function notification_logs()
    {
        return $this->hasMany('App\NotificationLog');
    }
    
}
