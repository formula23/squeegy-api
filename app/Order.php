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
        'tip',
        'tip_at',
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
        'tip_at',
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cancel_description()
    {
        return $this->belongsTo('App\CancelReason', 'cancel_reason');
    }
    
    public function sms_logs()
    {
        return $this->hasMany('App\OrderSmsLog');
    }
    
    /**
     * @param $query
     * @param $status
     * @return mixed
     */
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
        $existing_scheduled_orders_qry = self::whereIn('status', ['schedule','assign','start','done'])
            ->whereHas('schedule', function($q) {
            $q->whereDate('window_open', '>=', Carbon::today()->toDateString())->orderBy('window_open');
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

        $existing_scheduled_orders = $existing_scheduled_orders_qry->get();

        $current_schedule=[];
        foreach($existing_scheduled_orders as $existing_scheduled_order) {
            $key = $existing_scheduled_order->schedule->window_open->format('m/d/Y H');
            if(empty($current_schedule[$key])) $current_schedule[$key]=0;
            $current_schedule[$key]+=1;
        }
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
        $surcharge_record = $this->order_details()->where('name','like','%surcharge')->sum('amount');
        if(!$surcharge_record) return 0;
        return $surcharge_record;
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

    public function change_service($service_id)
    {
        $orig_price = $this->base_price();
        $orig_total = $this->total;
        $orig_credit = $this->credit;
        $orig_surcharge = $this->vehicleSurCharge();

        $order_credit = $this->order_credit()->where('status', 'auth')->first();

        //set new service level and reload the service relationship
        $this->service_id = (int)$service_id;
        $this->load('service');

        //////calc new values

        $this->etc = $this->get_etc();

//        $new_price = $this->base_price();
//        $this->price = $new_price;
        $this->price = $this->base_price();
        $new_price = $this->price;

        if($new_surcharge = $this->vehicleSurCharge()) {
            $this->price += $new_surcharge;
        }

        ///Apply discount to order with new service/price
        $this->applyPromoCode($this->promo_code);

        $new_total = $new_price - $this->discount - $orig_credit + $new_surcharge;

        $total_diff = $new_total - $orig_total;
//        print "total diff:".$total_diff."\n";

        ///if order has promo credits got applied during promo code logic
        if( $this->customer->availableCredit() && ! $this->promo_code) {
//            print "available credit:".$this->customer->availableCredit()."\n";
            $this->credit += min($total_diff, $this->customer->availableCredit());
        }

        $this->charged = $this->total;

//        $new_credit = $this->credit;

        $this->total = $this->price - $this->discount - $this->credit;
        $this->charged = $this->total;
//        $new_total = $this->total;

//        print "orig price: ".$orig_price."\n";
//
//        print "orig credit: ".$orig_credit."\n";
//        print "orig surcharge: ".$orig_surcharge."\n";
//        print "orig total: ".$orig_total."\n";
//        print "\n\n";
//        print "new price: ".$this->price."\n";
//        print "new disc: ".$this->discount."\n";
//        print "new credit: ".$this->credit."\n";
//        print "new surcharge: ".$new_surcharge."\n";
//        print "new total:".$new_total;
//        print "\n\n";

        $order_details=[];

        if($orig_price != $new_price) {
            $price_diff = $new_price - $orig_price;
            $direction = ($new_price > $orig_price ? "Upgrade" : "Downgrade" );
            $order_details[] = new OrderDetail(['name'=>$direction." to ".$this->service->name, 'amount'=>$price_diff]);
//            print "price diff: ".$price_diff."\n";
        }

        if($orig_surcharge != $new_surcharge) {
            $surcharge_diff = $new_surcharge - $orig_surcharge;
            $order_details[] = new OrderDetail(['name'=>$this->service->name." ".$this->vehicle->type." Surcharge", 'amount'=>$surcharge_diff]);
//            print "surcharge diff: ".$surcharge_diff."\n";
        }

//        print_r($order_details);
//
//        dd('done');

        $this->order_details()->saveMany($order_details);

        if($order_credit) {
            $order_credit->amount = -$this->credit;
            $order_credit->save();
        } else if($this->credit) {
            $this->order_credit()->save(new Credit(['user_id'=>$this->user_id, 'amount'=>-$this->credit, 'status'=>'auth']));
        }

        $this->save();
    }

    public function applyPromoCode($promo_code)
    {
//        if(Auth::user()->is('customer') && Auth::id()!=$this->user_id) {
//            return $this->response->errorNotFound('Order not found');
//        }

        if (empty($promo_code)) return "";

//        Log::info("partner:.....");
//        Log::info($order->partner);

        if($this->isPartner() && !$this->partner->allow_promo) {
            return trans('messages.order.discount.partners');
        }

        //check if promo code is a referral code
        if($referrer = User::where('referral_code', $promo_code)->where('id', '!=', $this->user_id)->first())
        {
            //referrer program only valid for new customers
            if( ! $this->customer->firstOrder()) return trans('messages.order.discount.referral_code_new_customer');

            $this->referrer_id = $referrer->id;
            $this->promo_code = $promo_code;
            $this->discount = (int)Config::get('squeegy.referral_program.referred_amt');
        }
        else
        {
            $discount = Discount::validate_code($promo_code, $this);

            if($discount === null) return trans('messages.order.discount.unavailable');

            if($discount->new_customer && ! $this->customer->firstOrder()) return trans('messages.order.discount.new_customer');

            if($discount->user_id && ($this->user_id != $discount->user_id)) return trans('messages.order.discount.unavailable');

            if(Discount::has_regions($discount->id) && ! $discount->regions->count()) return trans('messages.order.discount.out_of_region');

            if($discount->services->count() && ! in_array($this->service_id, $discount->services->lists('id')->all())) return trans('messages.order.discount.invalid_service', ['service_name' => $this->service->name]);

            $scope_discount = true;
            $frequency_rate = 0;
            if($discount->scope == "system") {
                $scope_label="";
                if($discount->frequency_rate && $discount->frequency_rate <= $discount->active_orders->count()) {
                    $scope_discount = false;
                    $frequency_rate = $discount->frequency_rate;
                }

                if($discount->discount_code) {
                    $actual_discount_code = $discount->actual_discount_code($promo_code);
                    if( ! $actual_discount_code) return trans('messages.order.discount.unavailable');

                    if($actual_discount_code->frequency_rate &&
                        $actual_discount_code->frequency_rate <= Order::where('promo_code', $promo_code)->whereNotIn('status', ['cancel','request'])->count())
                    {
                        $frequency_rate = $actual_discount_code->frequency_rate;
                        $scope_discount = false;
                    }
                }
            } else {
                $scope_label=" per customer";

                if($discount->discount_code) {
                    $actual_code = $discount->actual_discount_code($promo_code);
                    if(!$actual_code) return trans('messages.order.discount.unavailable');

                    if($actual_code->frequency_rate > 0) {

                        if( ! (Order::device_orders('promo_code', $promo_code)->count() < $actual_code->frequency_rate) ||
                            ! ($this->customer->orders_with_discount('promo_code', $promo_code)->count() < $actual_code->frequency_rate))
                        {
                            $frequency_rate = $actual_code->frequency_rate;
                            $scope_discount = false;
                        }
                    }
                }

                if($discount->frequency_rate) {
                    if( ! (Order::device_orders('discount_id', $discount->id)->count() < $discount->frequency_rate) ||
                        ! ($this->customer->orders_with_discount('discount_id', $discount->id)->count() < $discount->frequency_rate))
                    {
                        $frequency_rate = $discount->frequency_rate;
                        $scope_discount = false;
                    }
                }
            }

            if( ! $scope_discount) {
                switch($frequency_rate) {
                    case 1:
                    case 2:
                        $word_map = ['once','twice'];
                        $times = $word_map[($frequency_rate-1)];
                        break;
                    default:
                        $times = $frequency_rate." ".str_plural('time', $frequency_rate);
                        break;
                }
                return trans('messages.order.discount.frequency', ['times'=>$times, 'scope_label'=>$scope_label]);
            }

            //calculate discount
            $this->discount_id = $discount->id;
            $this->promo_code = $promo_code;

            if( $discount->discount_type=='amt' ) {
                $this->discount = $discount->amount;
            } else {
                $this->discount = (int) ($this->price * ($discount->amount / 100));
            }

            if($this->discount > $this->price) $this->discount = $this->price;
        }

//        $available_credit = ( ! $this->isPartner()) ? $this->customer->availableCredit() : 0 ;
        $available_credit = $this->customer->availableCredit();

        if($this->credit && 
            Config::get('squeegy.order_seq')[$this->status] < 6 && 
            ($this->getOriginal('service_id') != $this->service_id))
        {
            $available_credit += $this->credit;
        } else {
//            $available_credit = $this->credit;
        }

        $this->credit = min($this->price - $this->discount, $available_credit);
        $this->total = max(0,$this->price - $this->discount - $this->credit);

//        $available_credit = ( ! $order->isPartner()) ? $order->customer->availableCredit() : 0 ;
//        $order->credit = min($order->price - $order->discount, $available_credit);

    }

    public function base_price()
    {
        if( ! $this->service_id) return 0;

        if($this->isPartner()) {
            return $this->partner->services()->where('id', $this->service_id)->first()->pivot->price;
        } else {
            return $this->service->price;
        }
    }

    public function phone_numbers_in_use()
    {
        return $this->whereIn('status', ['assign','enroute','start'])
            ->whereNotNull('phone')
            ->where('id', '!=', $this->id)
            ->lists('phone', 'id');
    }

    public static function getOrderFromNumber($number)
    {
        return static::where('phone', $number)->first();
    }

    public function getContactRecipients($number)
    {
        $washer = $this->worker;
        $customer = $this->customer;
        
        if ($number === $customer->phone) {
            return [
                'from'=>$customer,
                'to'=>$washer,
            ];
        } else if($number === $washer->phone) {
            return [
                'from'=>$washer,
                'to'=>$customer,
            ];
        } else {
            return [];
        }
    }

    public function save_sms_log($order_recipients, $messageBody)
    {
        return $this->sms_logs()->create([
            'from' => $order_recipients['from']->id,
            'to' => $order_recipients['to']->id,
            'message' => $messageBody,
        ]);   
    }

}
