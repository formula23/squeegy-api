<?php namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use GeometryLibrary\PolyUtil;

class Partner extends Model
{
    protected $fillable = ['name', 'location_name', 'location', 'geo_fence', 'allow_promo', 'is_active','code','description'];

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

    /**
     * @return mixed
     */
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

    /**
     * @param $value
     * @return mixed
     */
    public function getGeoFenceAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * @return string
     */
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
        $partners = static::where('is_active', 1)->get();

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


    /**
     * @param $geo_fence
     * @return array
     */
    public static function format_coords($geo_fence)
    {
        $polygon=[];

        foreach((array)$geo_fence as $coord) {
            $polygon[] = ['lat'=>$coord->lat, 'lng'=>$coord->lng];
        }

        return $polygon;
    }

    /**
     * @param $requested_date
     * @return mixed
     */
    public function get_day_by_date($requested_date)
    {
        try {
            $day = $this->days()->whereDate('open', '=', $requested_date->toDateString())->first();
            return $day;
        } catch(\Exception $e) {
            \Bugsnag::notifyException($e);
        }
    }

    /**
     * @return bool
     */
    public function accepting_orders()
    {
        return (bool)$this->is_active;
    }

    /**
     * @return mixed
     */
    public function upcoming_date()
    {
        return $this->days()->orderBy('open')->first()->next_date_on_site();
    }

}
