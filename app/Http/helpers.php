<?php
use App\Order;
use Carbon\Carbon;

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
        '104.174.111.129', //dan home wifi
        '104.32.54.86', //squeegy office
        '24.205.12.22', //kevin
    ]);
}

function eta_real_time(Order $order, $round = 10)
{
    try {
        if( ! empty($order->confirm_at)) {
            $exact_eta = $order->confirm_at->addMinutes($order->eta);
            $arrival_time = Carbon::createFromTimestamp(ceil(strtotime($exact_eta->format('Y-m-d H:i')) / ($round * 60)) * ($round * 60));
            return $arrival_time->format('g:i a');
        }
    } catch (\Exception $e) {
        \Bugsnag::notifyException($e);
    }

    return "";
}