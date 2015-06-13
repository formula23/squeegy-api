<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/12/15
 * Time: 00:25
 */

namespace App\OctaneLA\Transformers;

use App\Order;
use League\Fractal\TransformerAbstract;

class OrderTransformer extends TransformerAbstract {

    public function transform(Order $order)
    {
        return [

            'links' => [
                [
                    'rel' => 'self',
                    'uri' => route('api.v1.orders.show', ['orders'=>$orders->id])
                ]
            ],
        ];
    }
}
