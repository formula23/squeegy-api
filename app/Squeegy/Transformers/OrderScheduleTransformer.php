<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 1/23/16
 * Time: 01:09
 */

namespace app\Squeegy\Transformers;


use App\OrderSchedule;
use League\Fractal\TransformerAbstract;

class OrderScheduleTransformer extends TransformerAbstract
{

    public function transform(OrderSchedule $orderSchedule)
    {
        return [
            'window_open' => $orderSchedule->window_open,
            'window_close' => $orderSchedule->window_close,
            'day' => ($orderSchedule->window_open ? $orderSchedule->window_open->format('D') : null),
            'date' => ($orderSchedule->window_open ? $orderSchedule->window_open->format('n/d') : null),
            'time_slot' => ($orderSchedule->window_open ? $orderSchedule->window_open->format('g')."-".$orderSchedule->window_close->format('ga') : null),
        ];

    }

}