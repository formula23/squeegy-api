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



function eta_real_time(Order $order)
{
    $exact_eta = $order->confirm_at->addMinutes($order->eta);
    $arrival_time = Carbon::createFromTimestamp(ceil($exact_eta->timestamp / (15 * 60)) * (15 * 60));
    return $arrival_time->format('g:i a');
}