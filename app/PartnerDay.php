<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PartnerDay extends Model
{
    protected $fillable = ['partner_id','day','day_of_week','next_date','time_start','time_end','order_cut_off_time','frequency','order_cap'];

    protected $dates = ['next_date'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner()
    {
        return $this->belongsTo('App\Partner');
    }
}
