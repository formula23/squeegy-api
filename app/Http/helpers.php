<?php
use App\Order;
use App\Squeegy\Orders;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Tests\ParameterBagTest;

/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/17/15
 * Time: 14:50
 * @param Order $order
 * @return string
 */

function is_internal()
{
    return in_array(Request::getClientIp(), [
        '127.0.0.1',
        '76.94.204.22', //dan home wifi
        '104.32.54.86', //squeegy office
        '24.205.12.22', //kevin
        '24.199.45.29', //saleh hotel
    ]);
}

function eta_real_time(Order $order, $round = 10)
{
    try {
        if( ! empty($order->confirm_at)) {
            $exact_eta = $order->confirm_at->addMinutes($order->eta);
            return real_time($exact_eta, $round);
//            $arrival_time = Carbon::createFromTimestamp(ceil(strtotime($exact_eta->format('Y-m-d H:i')) / ($round * 60)) * ($round * 60));
//            return $arrival_time->format('g:i a');
        }
    } catch (\Exception $e) {
        \Bugsnag::notifyException($e);
    }

    return "";
}

function real_time(Carbon $time, $round=10)
{
    $arrival_time = Carbon::createFromTimestamp(ceil(strtotime($time->format('Y-m-d H:i')) / ($round * 60)) * ($round * 60));
    return $arrival_time->format('g:i a');
}

function current_eta(Order $order)
{
    $order_seq = Config::get('squeegy.order_seq');

    try {

        \Log::info('Get Current ETA:');
        \Log::info('Order status: '.$order->status);
        $current_eta="";
        switch($order_seq[$order->status])
        {
            case 4: //enroute

//                try {
                    $origin = implode(",", [$order->worker->current_location->latitude, $order->worker->current_location->longitude]);
                    $destination = implode(",", [$order->location['lat'], $order->location['lon']]);
                    $travel_time = Orders::getRealTravelTime($origin, $destination);
                    \Log::info('Origin: '.$origin);
                    \Log::info('Destination: '.$destination);
                    \Log::info('Travel Time: '.$travel_time);
                    $eta=Carbon::now()->addMinutes($travel_time);
                    $current_eta = real_time($eta, 5);
//                } catch(\Exception $e) {}

                break;
            case 5:
            case 6:
                $current_eta = $order->start_at->format('g:i a');
                break;
            default:
                $current_eta = "";
                break;
        }
        \Log::info($current_eta);

    } catch (\Exception $e) {
        \Bugsnag::notifyException($e);
    }

    return $current_eta;

//    if($order->worker && $order_seq[$order->status] > 3) {
//
//
//        if(in_array()$order_seq[$order->status] >= 5) {
//            \Log::info('Job status: '.$order->status);
//            return $order->start_at->format('g:i a');
//        }
//
//
//
//    } else {
//        return "";
//    }
}