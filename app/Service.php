<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model {

    protected $fillable = ['name', 'price', 'details', 'time'];

    public function getDetailsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function discounts()
    {
        return $this->belongsToMany('App\Discount');
    }

}
