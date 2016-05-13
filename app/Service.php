<?php namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Service extends Model {

    protected $fillable = ['name', 'price', 'details', 'time', 'time_label', 'sequence', 'is_active'];

    protected $date;

    protected $mid_week_special = [
        1 => 1800,
        2 => 2500,
        3 => 1500,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->date = Carbon::now();
    }

    /**
     * @return mixed
     */
    public static function getAvailableServices()
    {
        $services = Service::where('is_active', 1)->orderBy('sequence')->get();

        return $services;
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
     * @return $this
     */
    public function partners()
    {
        return $this->belongsToMany('App\Partner')->withPivot('price');
    }

    /**
     * @param Carbon $date
     * @return mixed
     */
    public function price(Carbon $date=null)
    {
        if( ! empty($this->pivot) && $this->pivot->price) return $this->pivot->price;

        if($date) $this->date = $date;
        if($this->isMidWeekSpecial()) {
            return $this->mid_week_special[$this->id];
        } else {
            return $this->price;
        }
    }
    
    /**
     * @param $query
     * @param $type
     * @param $size
     * @return mixed
     */
    public function attribDetails($type, $size)
    {
//        $type = ($type!="Car" ? "Non-Car" : $type);
        return $this->attribs()->where('vehicle_type', $type)->where('vehicle_size', $size);
    }

    /**
     * @return bool
     */
    public function isMidWeekSpecial()
    {
        return in_array($this->date->dayOfWeek, [2,3]) ? true : false ;
    }


}
