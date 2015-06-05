<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model {

	protected $fillable = ['user_id', 'year', 'make', 'color', 'type', 'license_plate'];

}
