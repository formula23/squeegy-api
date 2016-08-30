<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Credit extends Model {

	protected $fillable = [
        'user_id',
        'order_id',
        'amount',
        'status',
        'description',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function order()
    {
        return $this->belongsTo('App\Order');
    }

}
