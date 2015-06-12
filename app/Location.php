<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model {

    protected $fillable = ['user_id', 'address1', 'address2', 'city', 'state', 'zip', 'lat', 'lng'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
