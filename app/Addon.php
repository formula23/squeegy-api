<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $fillable = ['name','description','price'];
    
    public function services() {
        return $this->belongsToMany('App\Service');
    }
    
}
