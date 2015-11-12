<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model {

	protected $fillable = ['name', 'code'];

    public function regions()
    {
        return $this->hasMany('App\Region');
    }

    public function users()
    {
        return $this->belongsToMany('App\User');
    }


}
