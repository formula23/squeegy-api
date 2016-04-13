<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceAttrib extends Model
{

    protected $fillable = ['vehicle_type', 'vehicle_size', 'etc', 'surcharge'];

    public function service()
    {
        return $this->belongsTo('App\Service');
    }
}
