<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {

    protected $fillable = [
        'user_id',
        'worker_id',
        'service_id',
        'vehicle_id',
        'job_number',
        'status',
        'eta',
        'location',
        'instructions',
        'confirm_at',
        'enroute_at',
        'start_at',
        'end_at',
        'photo_count',
        'price',
        'discount',
        'charged',
        'promo_code',
        'rating',
        'rating_comment',
        ];

    protected $attributes = array(
        'status' => 'request'
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

    public function worker()
    {
        return $this->belongsTo('App\User', 'worker_id');
    }

    public function vehicle()
    {
        return $this->belongsTo('App\Vehicle');
    }

}
