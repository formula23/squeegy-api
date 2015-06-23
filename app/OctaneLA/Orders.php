<?php namespace App\OctaneLA;
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/15/15
 * Time: 18:18
 */

use App\Order;
use Carbon\Carbon;

/**
 * Class Orders
 * @package App\OctaneLA
 */
class Orders {

    const CLOSING_THRESHOLD = 20;
    const BASE_LEAD_TIME = 30;
    const SUV_SURCHARGE = 500;
    const SUV_SURCHARGE_MULTIPLIER = 2;

    private static $order_status_time_map = [
        'confirm' => 60,
        'enroute' => 40,
        'start' => 20,
    ];

    /**
     * @return bool
     */
    public static function open()
    {
        $curr_hr = Carbon::now()->hour;
        if($curr_hr >= config('squeegy.operating_hours.open') && $curr_hr < config('squeegy.operating_hours.close')) return true;
        return false;
    }

    /**
     * @param Order $order
     * @return int
     */
    public static function getPrice(Order $order)
    {
        return $order->service->price;

        $base_price = $order->service->price;

        switch($order->vehicle->type)
        {
            case "SUV":
                $base_price += self::SUV_SURCHARGE;
                break;
            case "SUV+":
            case "Truck":
            case "Van":
                $base_price += self::SUV_SURCHARGE * self::SUV_SURCHARGE_MULTIPLIER;
                break;
        }

        return $base_price;
    }

    /**
     * Get the lead time to perform next order based on operating hours and open order status
     * Return time in minutes
     *
     * @param Order $order
     * @return int
     */
    public static function getLeadTime(Order $order = null)
    {
        if(self::remainingBusinessTime() < self::CLOSING_THRESHOLD && ! $order) {
            return 0;
        }

        $orders_in_q = Order::query();
        $orders_in_q->whereIn('status', ['confirm','enroute','start']);

        if($order && $order->confirm_at) {
            $orders_in_q->where('confirm_at', '<', $order->confirm_at);
        }

        $orders = $orders_in_q->get();

        if( ! $orders->count()) {
            return self::BASE_LEAD_TIME;
        }

        $leadtime = self::BASE_LEAD_TIME;
        foreach($orders as $order) {
            $leadtime += self::$order_status_time_map[$order->status];
        }

        if(self::remainingBusinessTime() < $leadtime) {
            return 0;
        }

        return $leadtime;
    }

    public static function formatConfirmEta($leadtime) {

        if($leadtime < 60) {
            return $leadtime." minutes";
        }
        $hrs = floor($leadtime/60);
        return $hrs." ".str_plural("hour", $hrs)." ".($leadtime % 60)." mins";
    }

    public static function formatLeadTime($leadtime)
    {
        if($leadtime < 60) {
            return [
                'time'=>(string)$leadtime,
                'time_label'=>'mins'
            ];
        }

        $t = $leadtime/60;

        return [
            'time' => (string)(is_float($t) ? floor($t)."+" : $t ),
            'time_label'=>'hour'
        ];
    }

    /**
     * Get the remaining business hours in minutes
     *
     * @return int
     */
    public static function remainingBusinessTime()
    {
        return Carbon::createFromTime(\Config::get('squeegy.operating_hours.close'),0,0)->diffInMinutes();
    }

}