<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

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

    /**
     * @return bool
     */
    public function hasSurCharge()
    {
//        if($this->type == "Car") return false;
//        if(in_array($this->type, ['SUV','Truck']) && $this->size == "Large") return true;
//        if(in_array($this->type, ['Minivan','Van'])) return true;
//        return false;
        if( ! $this->type) return false;
        //get Service attribs
        $service_attrib = ServiceAttrib::where('vehicle_type', $this->type)->first();
        return ($service_attrib->surcharge > 0 ? true : false);
    }

    public function full_name()
    {
        return $this->year." ".$this->make." ".$this->model;
    }
    
}
