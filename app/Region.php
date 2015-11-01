<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model {

	protected $fillable = ['postal_code'];

    public function users()
    {
        return $this->belongsToMany('App\User');
    }

}
