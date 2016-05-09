<?php namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Discount
 * @package App
 */
class Discount extends Model {

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'discount_type',
        'amount',
        'code',
        'start_at',
        'end_at',
        'new_customer',
        'scope',
        'frequency_rate',
        'is_active',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'start_at',
        'end_at',
    ];

    /**
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('discounts.is_active', 1);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany('App\Order');
    }

    /**
     * @return mixed
     */
    public function active_orders()
    {
        return $this->hasMany('App\Order')->whereNotIn('status', ['cancel','request']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function services()
    {
        return $this->belongsToMany('App\Service');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function regions()
    {
        return $this->hasMany('App\DiscountRegion');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function codes()
    {
        return $this->hasMany('App\DiscountCode');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function discount_code()
    {
        return $this->hasOne('App\DiscountCode');
    }

    /**
     * @param $code
     * @return mixed
     */
    public function actual_discount_code($code)
    {
        return $this->hasOne('App\DiscountCode')->where('code', $code)->first();
    }

    /**
     * @param $id
     * @return mixed
     */
    static public function has_regions($id)
    {
        return self::findOrFail($id)->regions->count();
    }

    /**
     * @param $code
     * @param Order $order
     * @return mixed
     */
    static public function validate_code($code, Order $order)
    {

        $order_date = ( $order->isSchedule() ? $order->schedule->window_open : $order->confirm_at );

//        $discount_qry = self::leftJoin('discount_codes', 'discounts.id', '=', 'discount_codes.discount_id')->active();
        $discount_qry = self::select('discounts.id', 'discounts.user_id', 'discounts.discount_type', 'discounts.amount', 'new_customer', 'scope', 'discounts.frequency_rate', 'single_use_code')
            ->leftJoin('discount_codes', 'discounts.id', '=', 'discount_codes.discount_id')
            ->leftJoin('discount_user', 'discounts.id', '=', 'discount_user.discount_id')
            ->active()
            ->where(function($q) use ($order) {
                $q->whereNull('discount_user.user_id')
                    ->orWhere('discount_user.user_id', $order->user_id);
            })
            ->where(function($q) use ($order_date) {
                $q->whereNull('start_at')
                    ->orWhere('start_at', '<=', $order_date);
            })
            ->where(function($q) use ($order_date) {
                $q->whereNull('end_at')
                    ->orWhere('end_at', '>=', $order_date);
            })
            ->where(function($q) use ($code) {
                $q->where('discounts.code', $code)
                    ->orWhere('discount_codes.code', $code);
            })
            ->where(function($q) {
                $q->where('discount_codes.is_active', 1)
                    ->orWhereNull('discount_codes.is_active');
            })
            ->with(['regions' => function($q) use ($order) {
                $q->where('postal_code', $order['location']['zip']);
            }])
            ->with('discount_code')
            ->groupBy('discounts.id');

        return $discount_qry->get()->first();
    }

}
