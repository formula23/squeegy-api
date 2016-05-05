<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = ['name', 'location', 'geo_fence'];

    /**
     * @return $this
     */
    public function services()
    {
        return $this->belongsToMany('App\Service')->withPivot('price');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function days()
    {
        return $this->hasMany('App\PartnerDay');
    }

}
