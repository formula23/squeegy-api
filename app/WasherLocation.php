<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class WasherLocation extends Model {

    protected $fillable = ['latitude', 'longitude'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
