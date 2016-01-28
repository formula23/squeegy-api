<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model {

    use SoftDeletes;

    protected $dates = ['deleted_at'];

	protected $fillable = ['user_id','identifier','card_type','last4','exp_month','exp_year','is_default'];

    public function user()
    {
        $this->belongsTo('App\User');
    }

}
