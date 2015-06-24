<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model {

    protected $fillable = ['name', 'price', 'details', 'time'];

    public function getDetailsAttribute($value)
    {
        return json_decode($value, true);
    }

}
