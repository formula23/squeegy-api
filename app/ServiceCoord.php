<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceCoord extends Model {

	protected $fillable = ['lat', 'lng'];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

}
