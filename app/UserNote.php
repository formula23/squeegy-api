<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserNote extends Model
{
    protected $fillable = ['user_id','note'];
    
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
