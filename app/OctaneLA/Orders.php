<?php namespace App\OctaneLA;
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/15/15
 * Time: 18:18
 */

use App\Order;
use Carbon;

/**
 * Class Orders
 * @package App\OctaneLA
 */
class Orders {

    /**
     * @var Order
     */
    protected $order;
    /**
     * @var int
     */
    protected $base_lead_time = 30;
    /**
     * @var int
     */
    protected $suv_surcharge = 500;
    /**
     * @var int
     */
    protected $suv_surcharge_multiplier = 2;

    /**
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return bool
     */
    public function areWeOpen()
    {
        $curr_time = Carbon\Carbon::now()->hour;
        if($curr_time >= \Config::get('squeegy.operating_hours.open') && $curr_time <= \Config::get('squeegy.operating_hours.close')) return true;
        return false;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        $base_price = $this->order->service->price;

        switch($this->order->vehicle->type)
        {
            case "SUV":
                $base_price += $this->suv_surcharge;
                break;
            case "SUV+":
            case "Truck":
            case "Van":
                $base_price += $this->suv_surcharge * $this->suv_surcharge_multiplier;
                break;
        }

        return $base_price;
    }

    /**
     * @return int
     */
    public function getLeadTime()
    {
        //how many in order Q
        $this->base_lead_time;
        $orders = Order::where('status', 'accept')->get();

        return $this->base_lead_time * ($orders->count() ? $orders->count() : 1);
    }

}