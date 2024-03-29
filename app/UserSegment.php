<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSegment extends Model {

	protected $fillable = ['user_id','segment_id','subscriber_at','user_at','customer_at','repeat_customer_at','advocate_at', 'last_wash_at'];

    protected $dates = ['subscriber_at','user_at','customer_at','repeat_customer_at','advocate_at','last_wash_at'];


    public function segment()
    {
        return $this->belongsTo('App\Segment');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
