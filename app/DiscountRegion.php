<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class DiscountRegion extends Model {

    public function discount()
    {
        return $this->belongsTo('App\Discount', 'discount_id');
    }

}
