<?php namespace App;

use Bican\Roles\Contracts\HasRoleAndPermissionContract;

use Bican\Roles\Traits\HasRoleAndPermission;
//use Bican\Roles\Contracts\HasRoleAndPermission as HasRoleAndPermissionContract;

use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

/**
 * Class User
 * @package App
 */
class User extends Model implements AuthenticatableContract, CanResetPasswordContract, HasRoleAndPermissionContract {

	use Authenticatable, CanResetPassword, HasRoleAndPermission;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

	protected $fillable = ['name', 'email', 'password', 'phone', 'photo', 'stripe_customer_id', 'push_token', 'target_arn_gcm', 'facebook_id', 'is_active', 'app_version', 'referral_code', 'anon_pw_reset', 'device_id'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

    public $device_orders = null;

    /**
     * A user can have many vehicles
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function vehicles()
    {
        return $this->hasMany('App\Vehicle');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        $foreign_key = ( ! empty(\Auth::user()) && \Auth::user()->is('worker') ? 'worker_id' : null );
        return $this->hasMany('App\Order', $foreign_key);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function referral_orders()
    {
        return $this->hasMany('App\Order', 'referrer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function credits()
    {
        return $this->hasMany('App\Credit');
    }

    /**
     * @return int
     */
    public function availableCredit()
    {
        return (int)$this->credits()->where('status', '!=', 'void')->sum('amount');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobs()
    {
        return $this->hasMany('App\Order', 'worker_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function discounts()
    {
        return $this->belongsToMany('App\Discount');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function zones()
    {
        return $this->belongsToMany('App\Zone');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function segment()
    {
        return $this->hasOne('App\UserSegment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function default_location()
    {
        return $this->hasOne('App\WasherDefaultLocation');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function current_location()
    {
        return $this->hasOne('App\WasherLocation');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activity_logs()
    {
        return $this->hasMany('App\WasherActivityLog');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activity()
    {
        return $this->hasMany('App\ActivityLog');
    }

    public function scopePastOrders($query)
    {
        return $query->whereHas('orders', function($q) {
            $q->where('status', 'done');
        });
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeWorkers($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'Worker');
        });
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeCustomers($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'Customer');
        });
    }

    public function scopeActiveWashers($query, $postal_code)
    {
        $query = self::scopeWorkers($query);

        $today = Carbon::today()->toDateString();

        $query->with(['jobs' => function ($q) use ($today) {
            $q->whereIn('status', ['assign','enroute','start'])
                ->whereDate('confirm_at', '=', $today)
                ->orderBy('confirm_at');
        }])
        ->with(['default_location' => function($q) {
            $q->select('user_id', 'latitude', 'longitude');
        }])
        ->with(['current_location' => function($q) {
            $q->select('user_id', 'latitude', 'longitude');
        }])
        ->whereHas('activity_logs', function($q) {
            $q->whereNull('log_off');
        })
        ->whereHas('zones.regions', function($q) use ($postal_code) {
            $q->where('postal_code', $postal_code);
        });

        return $query;
    }

    /**
     * @param $value
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = ( ! empty($value) ? "+1".$value : "" );
    }

    /**
     * @param $password
     */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = \Hash::make($password);
    }

    /**
     * @return bool
     */
    public function firstOrder()
    {
        $this->device_orders = Order::device_orders();
        if($this->device_orders->count()) return false;

        $prev_orders = $this->orders()->whereNotIn('orders.status', ['cancel','request'])->get();
        if($prev_orders->count()) return false;
        else return true;
    }

    /**
     * @param $col
     * @param $val
     * @return mixed
     */
    public function orders_with_discount($col, $val)
    {
        return $this->hasMany('App\Order')->where($col, $val)->whereNotIn('status', ['cancel','request']);
    }

    /**
     * @return string
     */
    public static function generateReferralCode()
    {
        while(true) {
            $referral_code = strtoupper(substr( md5(rand()), 0, 5));
            $usr = self::where('referral_code', $referral_code)->get();
            if(!$usr->count()) break;
        }
        return $referral_code;
    }



}
