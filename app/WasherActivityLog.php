<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class WasherActivityLog extends Model {

    protected $fillable = ['log_on', 'log_off', 'login', 'logout'];

	public function user()
    {
        return $this->belongsTo('App\User');
    }

}
