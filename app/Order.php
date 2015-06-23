<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Order
 * @package App
 */
class Order extends Model {

    /**
     * @var array
     */
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
        'done_at',
        'photo_count',
        'price',
        'discount',
        'charged',
        'promo_code',
        'rating',
        'rating_comment',
        ];

    /**
     * @var array
     */
    protected $attributes = array(
        'status' => 'request'
    );

    /**
     * @param $value
     * @return mixed
     */
    public function getLocationAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * @param $value
     */
    public function setLocationAttribute($value)
    {
        $this->attributes['location'] = json_encode($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function worker()
    {
        return $this->belongsTo('App\User', 'worker_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo('App\Service');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vehicle()
    {
        return $this->belongsTo('App\Vehicle');
    }

}
