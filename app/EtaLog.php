<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class EtaLog extends Model {

    protected $fillable = ['user_id', 'eta', 'city', 'state', 'postal_code', 'latitude', 'longitude', 'message'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
