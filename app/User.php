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
	protected $fillable = ['name', 'email', 'password', 'phone', 'photo', 'stripe_customer_id', 'push_token', 'facebook_id'];

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

    public function scopeWorkers($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'Worker');
        });
    }

    public function scopeCustomers($query)
    {
        return $query->whereHas('customers', function ($q) {
            $q->where('name', 'Customer');
        });
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = "+1".$value;
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = \Hash::make($password);
    }

}
