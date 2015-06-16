<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {

    protected $fillable = ['user_id', 'washer_id', 'service_id', 'vehicle_id', 'job_number', 'location', 'lead_time', 'instructions', 'status'];

    protected $attributes = array(
        'status' => 'decline'
    );

    public function getLocationAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setLocationAttribute($value)
    {
        $this->attributes['location'] = json_encode($value);
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function service()
    {
        return $this->belongsTo('App\Service');
    }

    public function washer()
    {
        return $this->belongsTo('App\Washer');
    }

    public function vehicle()
    {
        return $this->belongsTo('App\Vehicle');
    }

}
