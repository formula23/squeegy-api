<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PartnerDay extends Model
{
    protected $fillable = ['partner_id','day','day_of_week','time_start','time_end'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner()
    {
        return $this->belongsTo('App\Partner');
    }
}
