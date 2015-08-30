<?php namespace App;

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

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function discount_regions()
    {
        return $this->hasMany('App\DiscountRegion')->select(['id', 'postal_code']);
    }

    public function discount_codes()
    {
        return $this->hasMany('App\DiscountCode')->select(['id', 'postal_code   ']);
    }

}
