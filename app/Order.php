<?php namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;

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

//    public $hasSurcharge = false;

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

    public function auth_transaction()
    {
        return $this->hasOne('App\Transaction')->where('type', 'auth');
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
        return $this->schedule->window_open->format('l');
    }

    /**
     * @return null|string
     */
    public function scheduled_time()
    {
        if(!$this->schedule) return null;
        return $this->schedule->window_open->format('g')."-".$this->schedule->window_close->format('ga');
    }

    public function scheduled_eta()
    {
        if(!$this->schedule) return null;
        return $this->schedule->window_open->format('D n/d')." @ ".$this->scheduled_time();
    }

    public function generated_revenue()
    {
        return ($this->charged > 0 || in_array($this->discount_id, [27,28,55,56,57,58]) ? true : false );
    }

    public static function current_scheduled_orders()
    {
        $existing_scheduled_orders = self::whereIn('status', ['schedule'])->whereHas('schedule', function($q) {
            $q->whereDate('window_open', '>', Carbon::now())->orderBy('window_open');
        })->with('schedule')->get();

        $current_schedule=[];
        foreach($existing_scheduled_orders as $existing_scheduled_order) {
            $key = $existing_scheduled_order->schedule->window_open->format('m/d/Y H');
            if(empty($current_schedule[$key])) $current_schedule[$key]=0;
            $current_schedule[$key]+=1;
        }

        return $current_schedule;

    }

    public static function device_orders($col=null, $val=null)
    {
        $collection = Collection::make([]);
        if(Request::header('X-Device-Identifier')) {
            $q = self::join('users', 'orders.user_id' , '=', 'users.id')
                ->where('users.device_id', Request::header('X-Device-Identifier'))
                ->whereNotIn('orders.status', ['cancel','request']);
            if($col && $val) {
                $q->where($col, $val);
            }
            return $q->get();
        }
        return $collection;
    }

    public function vehicleSurCharge()
    {
        if($this->vehicle->hasSurCharge()) {
            return (int)Config::get('squeegy.vehicle_surcharge.'.$this->service->id);
        }
        return 0;
    }

}
