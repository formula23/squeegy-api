<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 9/21/16
 * Time: 23:56
 */

namespace App\Squeegy\Transformers;


use App\Addon;
use App\OrderDetail;
use League\Fractal\TransformerAbstract;

class OrderDetailTransformer extends TransformerAbstract
{

    public function transform(OrderDetail $orderDetail)
    {
        return [
            "name" => $orderDetail->name,
            "amount" => $orderDetail->amount,
        ];
    }

}