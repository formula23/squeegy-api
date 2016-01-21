<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model {

	protected $fillable = ['order_id', 'charge_id', 'amount', 'type', 'last_four', 'card_type'];

    public function order()
    {
        return $this->belongsTo('App\Order');
    }

}
