<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use GeometryLibrary\PolyUtil;

class Partner extends Model
{
    protected $fillable = ['name', 'location_name', 'location', 'geo_fence', 'is_active'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany('App\Order');
    }

    /**
     * @return $this
     */
    public function services()
    {
        return $this->belongsToMany('App\Service')->withPivot('price');
    }

    public function users()
    {
        return $this->belongsToMany('App\User', 'orders')->where('orders.status', 'done');
    }

    /**
     * @param $service_id
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function service($service_id)
    {
        return $this->belongsToMany('App\Service')->withPivot('price')->wherePivot('service_id', $service_id);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function days()
    {
        return $this->hasMany('App\PartnerDay');
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getLocationAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * @param $value
     */
    public function setLocationAttribute($value)
    {
        $this->attributes['location'] = json_encode($value);
    }

    public function getGeoFenceAttribute($value)
    {
        return json_decode($value);
    }

    public function location_display()
    {
        return $this->location_name." ".$this->location['street'];
    }

    /**
     * @param $lat
     * @param $lng
     * @return \___PHPSTORM_HELPERS\static|mixed|null
     */

    public static function where_coords_in($lat, $lng)
    {
        $partners = static::all();

        foreach($partners as $partner) {

            $resp = PolyUtil::containsLocation(
                ['lat' => $lat, 'lng' => $lng], // point array [lat, lng]
                static::format_coords($partner->geo_fence)
            );

            if($resp) {
                return $partner;
            }
        }

        return null;
    }


    public static function format_coords($geo_fence)
    {
        $polygon=[];

        foreach((array)$geo_fence as $coord) {
            $polygon[] = ['lat'=>$coord->lat, 'lng'=>$coord->lng];
        }

        return $polygon;
    }



}
