<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['name', 'key', 'message'];

    public function notification_logs()
    {
        return $this->hasMany('App\NotificationLog');
    }
    
}
