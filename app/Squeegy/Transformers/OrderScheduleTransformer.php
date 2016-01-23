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
            'id' => (string)$orderSchedule->id,
            'window_open' => $orderSchedule->window_open,
            'window_close' => $orderSchedule->window_close,
        ];
    }

}