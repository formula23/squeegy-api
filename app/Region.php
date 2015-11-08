<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model {

	protected $fillable = ['zone_id', 'postal_code'];

    public function zone()
    {
        return $this->belongsTo('App\Zone');
    }

}
