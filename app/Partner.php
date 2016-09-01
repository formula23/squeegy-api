<?php namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use GeometryLibrary\PolyUtil;

class Partner extends Model
{
    protected $fillable = ['name', 'location_name', 'location', 'geo_fence', 'allow_promo', 'is_active'];

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


    public static function format_coords($geo_fence)
    {
        $polygon=[];

        foreach((array)$geo_fence as $coord) {
            $polygon[] = ['lat'=>$coord->lat, 'lng'=>$coord->lng];
        }

        return $polygon;
    }

    public function accepting_orders($requested_date)
    {
        $day = $this->days()->whereDate('next_date', '=', $requested_date->toDateString())->first();

//        $current_schedule = Order::current_scheduled_orders($this->id);
        $current_schedule = $this->current_scheduled_orders();

        \Log::info($this->id);
        \Log::info($requested_date);
        \Log::info('Current schedule.......');
        \Log::info($current_schedule);

        if( $day->order_cap > 0 &&
            isset($current_schedule[$requested_date->format('m/d/Y H')]) &&
            $current_schedule[$requested_date->format('m/d/Y H')] >= $day->order_cap )
        {
            return false;
        }
        
        return true;
    }
    
    public function current_scheduled_orders()
    {
        $existing_scheduled_orders_q = $this->orders()->whereIn('status', ['schedule','assign','start','done'])
            ->whereHas('schedule', function($q) {
                $q->whereDate('window_open', '>=', Carbon::today()->toDateString())->orderBy('window_open');
            })->with('schedule');

        \Log::info($existing_scheduled_orders_q->toSql());

        $existing_scheduled_orders = $existing_scheduled_orders_q->get();
\Log::info($existing_scheduled_orders);
        $current_schedule=[];
        foreach($existing_scheduled_orders as $existing_scheduled_order) {
            $key = $existing_scheduled_order->schedule->window_open->format('m/d/Y H');
            if(empty($current_schedule[$key])) $current_schedule[$key]=0;
            $current_schedule[$key]+=1;
        }
        return $current_schedule;
    }
    

}
