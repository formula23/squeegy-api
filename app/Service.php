<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model {

    protected $fillable = ['name', 'price', 'details', 'time', 'time_label', 'sequence', 'is_active'];

    /**
     * @return mixed
     */
    public static function getAvailableServices()
    {
        return Service::where('is_active', 1)->orderBy('sequence')->get();
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getDetailsAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function discounts()
    {
        return $this->belongsToMany('App\Discount');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attribs()
    {
        return $this->hasMany('App\ServiceAttrib');
    }
    
    /**
     * @param $query
     * @param $type
     * @param $size
     * @return mixed
     */
    public function attribDetails($type, $size)
    {
        $type = ($type!="Car" ? "Non-Car" : $type);
        return $this->attribs()->where('vehicle_type', $type)->where('vehicle_size', $size);
    }
}
