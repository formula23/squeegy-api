<?php namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
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
        'partner_id',
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

    /**
     * @return mixed
     */
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notification_logs()
    {
        return $this->hasMany('App\NotificationLog');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner()
    {
        return $this->belongsTo('App\Partner');
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

    public function scopeOfStatus($query, $status)
    {
        return $query->where('status', $status);
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
     * @return bool|null
     */
    public function isSubscription()
    {
        if(!$this->schedule) return null;
        return ($this->schedule->type=='subscription') ? true : false;
    }

    /**
     * @return bool
     */
    public function isPartner()
    {
        return ($this->partner ? true : false );
    }
    
    /**
     * @return bool|null
     */
    public function isSchedule()
    {
        if(!$this->schedule) return null;
        return ($this->schedule->type=='one-time') ? true : false;
    }

    /**
     * @return null|string
     */
    public function scheduled_time()
    {
        if(!$this->schedule->window_open) return null;
        return $this->schedule->display_time();
    }

    /**
     * @return null|string
     */
    public function scheduled_eta()
    {
        if(!$this->schedule) return null;
        return $this->schedule->window_open->format('D n/d')." @ ".$this->scheduled_time();
    }

    /**
     * @return bool
     */
    public function generated_revenue()
    {
        return ($this->charged > 0 || in_array($this->discount_id, array_keys(\Config::get('squeegy.groupon_gilt_promotions'))) ? true : false );
    }

    /**
     * @return mixed
     */
    public function revenue()
    {
        $charged = $this->charged;

        if(in_array($this->discount_id, array_keys($promo_prices = \Config::get('squeegy.groupon_gilt_promotions')))) {
            $charged = $promo_prices[$this->discount_id];
        }
        return $charged;
    }

    /**
     * @return string
     */
    public function arrival_eta()
    {
        return eta_real_time($this);
    }

    /**
     * @param mixed $partner_id
     * @return array
     */
    public static function current_scheduled_orders($partner_id = null)
    {
        $existing_scheduled_orders_qry = self::whereIn('status', ['schedule'])
            ->whereHas('schedule', function($q) {
            $q->whereDate('window_open', '>=', Carbon::now())->orderBy('window_open');
        })->with('schedule');

        if( ! $partner_id) {
            $existing_scheduled_orders_qry->whereNull('partner_id');
        } else {
            if(is_array($partner_id)) {
                $existing_scheduled_orders_qry->whereIn('partner_id', $partner_id);
            } else {
                $existing_scheduled_orders_qry->where('partner_id', $partner_id);
            }
        }

        \Log::info($existing_scheduled_orders_qry->toSql());

        $existing_scheduled_orders = $existing_scheduled_orders_qry->get();

        \Log::info('existing schedule...');
        \Log::info($existing_scheduled_orders);

        $current_schedule=[];
        foreach($existing_scheduled_orders as $existing_scheduled_order) {
            $key = $existing_scheduled_order->schedule->window_open->format('m/d/Y H');
            if(empty($current_schedule[$key])) $current_schedule[$key]=0;
            $current_schedule[$key]+=1;
        }



\Log::info($current_schedule);
        return $current_schedule;

    }

    /**
     * @param null $col
     * @param null $val
     * @return static
     */
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

    /**
     * @return int
     */
    public function vehicleSurCharge()
    {
        if( ! $this->vehicle->hasSurCharge()) return 0;
        
        if(empty($this->vehicle->type)) $this->vehicle->type = "Car";
        if(empty($this->vehicle->size)) $this->vehicle->size = "Midsize";
        $service_attrib = $this->service->attribDetails($this->vehicle->type, $this->vehicle->size)->first();
        return ( $service_attrib ? $service_attrib->surcharge : 0 );
    }

    public function hasSurCharge()
    {
        $surcharge_record = $this->order_details()->where('name','like','%surcharge')->first();
        if(!$surcharge_record) return 0;
        return $surcharge_record->amount;
    }

    public function get_etc()
    {
        $service_attrib = $this->service->attribDetails($this->vehicle->type, $this->vehicle->size)->first();
        return ( $service_attrib ? $service_attrib->etc : $this->service->time );
    }

    public function charges()
    {
        return $this->transactions()->whereIn('type', ['capture','sale'])->orderBy('amount', 'desc')->get();
    }

}
