<?php


namespace App;

use Bican\Roles\Traits\HasRoleAndPermission;
use Bican\Roles\Contracts\HasRoleAndPermission as HasRoleAndPermissionContract;

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

	protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'photo',
        'stripe_customer_id',
        'push_token',
        'target_arn_gcm',
        'facebook_id',
        'age_range',
        'birthday',
        'gender',
        'is_active',
        'app_version',
        'referral_code',
        'anon_pw_reset',
        'tmp_fb',
        'device_id',
    ];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

    public $device_orders = null;

    /**
     * @param $value
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = trim($value);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getNameAttribute($value)
    {
        return trim($value);
    }

    public function email_address() {
        if($this->is_anon() && $this->tmp_fb == 1) {
            return "";
        }
        return $this->email;
    }

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
        $foreign_key = ( $this->is('worker') ? 'worker_id' : null );
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

    public function partners()
    {
        return $this->belongsToMany('App\Partner', 'orders')->where('orders.status', 'done');
    }

    /**
     * @return int
     */
    public function availableCredit()
    {
        $avail_credits = (int)$this->credits()->where('status', '!=', 'void')->sum('amount');
        return ($avail_credits < 0 ? 0 : $avail_credits );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function eta_logs()
    {
        return $this->hasMany('App\EtaLog');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobs()
    {
        return $this->hasMany('App\Order', 'worker_id');
    }

    /**
     * @param null $after_job
     * @return mixed
     */
    public function active_jobs($after_job=null)
    {
        $q = $this->jobs()->whereIn('status', ['assign','enroute','start'])->orderBy('confirm_at');
        
        if($after_job) {
            $q->where('confirm_at', '>', $after_job->confirm_at)
                ->where('orders.id', '!=', $after_job->id);
        }
        return $q;
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifications()
    {
        return $this->hasMany('App\NotificationLog');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notes()
    {
        return $this->hasMany('App\UserNote');
    }
    
    /**
     * @param $query
     * @return mixed
     */
    public function completedPaidOrders()
    {
        return $this->orders()
            ->where('status', 'done')
            ->where(function($q) {
                $q->where('charged', '>', 0)->orWhereIn('discount_id', [27,28,55,56,57,58]);
            })
            ->orderBy('done_at');
    }

    /**
     * @return mixed
     */
    public function lastWash()
    {
        return $this->orders()->where('status','done')->orderBy('done_at', 'desc')->first();
    }

    /**
     * @return mixed
     */
    public function completedReferralOrders()
    {
        return $this->referral_orders()->where('status', 'done')->orderBy('done_at');
    }

    /**
     * @param $query
     * @return mixed
     */
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

    /**
     * @param $query
     * @param $postal_code
     * @return mixed
     */
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
            $q->whereNotNull('log_on')->whereNull('log_off');
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
        $this->attributes['phone'] = ( ! preg_match('/^\+1/', $value) ? "+1".$value : "" );
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


    /**
     * @return bool
     */
    public function is_anon()
    {
        return (bool)(preg_match('/squeegyapp-tmp.com$/', $this->email) || $this->tmp_fb);
    }

    /**
     * @return bool
     */
    public function is_tmp_email()
    {
        return (bool)(preg_match('/squeegyapp-tmp.com$/', $this->email));
    }
    
    /**
     * @return string
     */
    public function device()
    {
        $device = 'iOS';
        if($this->device_id && $this->target_arn_gcm) $device = "Android";
        return $device;

    }


    /**
     * @return bool
     */
    public function is_advocate()
    {
        return (($this->segment &&
                    $this->segment->customer_at &&
                    $this->completedReferralOrders()->count() >= 1 &&
                    $this->completedPaidOrders()->count() >= 3) ? true : false );
    }

    /**
     * @param $fb_user
     */
    public function updateFbFields($fb_user)
    {
        $this->facebook_id = $fb_user->getId();
        $this->birthday = $fb_user->getBirthday();
        $this->gender = $fb_user->getGender();

        $graph_node = $fb_user->getField('age_range');
        if($graph_node) {
            $min_age = $graph_node->getField('min');
            $max_age = $graph_node->getField('max');
            $age_range = ($max_age ? $min_age."-".$max_age : $min_age."+");
            $this->age_range = $age_range;
        }
        $this->save();
        return;
    }

    public function first_name()
    {
        if( ! $this->name) return '';
        $nameParts = explode(' ', $this->name);
        array_filter($nameParts);
        if(count($nameParts)>1) $lastName = array_pop($nameParts); //remove last name
        return trim(implode(' ', $nameParts));
    }

    public function last_name()
    {
        if(!$this->name) return '';
        $nameParts = explode(' ', $this->name);
        array_filter($nameParts);
        if(count($nameParts)>1) return trim(array_pop($nameParts));
        else return '';
    }

    
    public function open_orders()
    {
        return $this->orders()
            ->whereIn('status', ['assign','enroute','start'])
            ->whereDate('confirm_at', '=', Carbon::today()->toDateString())
            ->orderBy('confirm_at')
            ->get();
    }
    
}
