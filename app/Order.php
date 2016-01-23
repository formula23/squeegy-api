<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'referrer_id',
        'service_id',
        'vehicle_id',
        'job_number',
        'status',
        'eta',
        'etc',
        'location',
        'instructions',
        'confirm_at',
        'assign_at',
        'enroute_at',
        'start_at',
        'done_at',
        'cancel_at',
        'cancel_reason',
        'photo_count',
        'price',
        'discount',
        'credit',
        'total',
        'charged',
        'stripe_charge_id',
        'promo_code',
        'rating',
        'rating_comment',
        'push_platform',
        ];

    /**
     * @var array
     */
    protected $attributes = array(
        'status' => 'request'
    );


    protected $dates = [
        'confirm_at',
        'assign_at',
        'enroute_at',
        'start_at',
        'done_at',
        'cancel_at',
    ];

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
     * @return int
     */
    public function subTotal()
    {
        return (int)$this->order_details()->sum('amount');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function schedule()
    {
        return $this->hasOne('App\OrderSchedule');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function order_credit()
    {
        return $this->hasOne('App\Credit');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function order_details()
    {
        return $this->hasMany('App\OrderDetail');
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
    public function referrer()
    {
        return $this->belongsTo('App\User', 'referrer_id');
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
        return $this->belongsTo('App\Vehicle')->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function discount_record()
    {
        return $this->belongsTo('App\Discount', 'discount_id');
    }

    /**
     * @return null|string
     */
    public function scheduled_date()
    {
        if(!$this->schedule) return null;
        $open = new Carbon($this->schedule->window_open);
        return $open->format('m/d/Y');
    }

    /**
     * @return null|string
     */
    public function scheduled_day()
    {
        if(!$this->schedule) return null;
        $open = new Carbon($this->schedule->window_open);
        return $open->format('l');
    }

    /**
     * @return null|string
     */
    public function scheduled_time()
    {
        if(!$this->schedule) return null;
        $open = new Carbon($this->schedule->window_open);
        $close = new Carbon($this->schedule->window_close);
        return $open->format('g:ia')." - ".$close->format('g:ia');
    }

}
