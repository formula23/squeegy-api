<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model {

    protected $fillable = ['name', 'price', 'details', 'time', 'time_label', 'sequence', 'is_active'];

    public static function getAvailableServices()
    {
        return Service::where('is_active', 1)->orderBy('sequence')->get();
    }

    public function getDetailsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function discounts()
    {
        return $this->belongsToMany('App\Discount');
    }

}
