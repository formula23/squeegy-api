<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Segment extends Model {

	protected $fillable = ['name', 'description'];

	public function user_segment()
	{
		return $this->hasMany('App\UserSegment');
	}

}
