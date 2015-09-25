<?php namespace App;

use Bican\Roles\Contracts\HasRoleAndPermissionContract;

use Bican\Roles\Traits\HasRoleAndPermission;
//use Bican\Roles\Contracts\HasRoleAndPermission as HasRoleAndPermissionContract;

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
	protected $fillable = ['name', 'email', 'password', 'phone', 'photo', 'stripe_customer_id', 'push_token', 'facebook_id', 'is_active', 'app_version'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

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
        return $this->hasMany('App\Order');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobs()
    {
        return $this->hasMany('App\Order', 'worker_id');
    }

    public function discounts()
    {
        return $this->belongsToMany('App\Discount');
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
        return $query->whereHas('customers', function ($q) {
            $q->where('name', 'Customer');
        });
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
        $prev_orders = $this->orders()->whereNotIn('orders.status', ['cancel','request'])->get();
        if($prev_orders->count()) return false;
        else return true;
    }

    /**
     * @param Discount $discount
     * @return bool
     */
    public function discountEligible(Discount $discount)
    {
        if( ! $discount->frequency_rate) return true;
        return ($this->orders()->where(['discount_id'=>$discount->id, 'status'=>'done'])->get()->count() < $discount->frequency_rate);
    }
}
