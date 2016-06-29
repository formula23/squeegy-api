<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CancelReason extends Model
{
    protected $fillable = ['description'];
    
    public function orders()
    {
        return $this->hasMany('App\Order', 'cancel_reason');
    }
    
}
