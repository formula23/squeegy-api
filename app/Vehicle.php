<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model {

    use SoftDeletes;

    protected $dates = ['deleted_at'];

	protected $fillable = ['user_id', 'year', 'make', 'model', 'color', 'type', 'size', 'license_plate'];

    /**
     * A vehicle is owned by a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
