<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/12/15
 * Time: 00:25
 */

namespace App\OctaneLA\Transformers;

use App\OctaneLA\Orders;
use App\Order;
use League\Fractal\TransformerAbstract;

class OrderTransformer extends TransformerAbstract {

    protected $defaultIncludes = [
        'vehicle',
        'service',
        'worker'
    ];

    public function transform(Order $order)
    {
        return [
            'id' => (string)$order->id,
            'job_number' => $order->job_number,
            'status' => $order->status,
            'location' => $order->location,
            'instructions' => $order->instructions,
            'subtotal' => (int)$order->price,
            'discount' => (($order->discount)? $order->discount : null ),
            'total' => (int)($order->price - (int)$order->discount),
            'eta' => Orders::formatConfirmEta($order->eta),
            'completed_time' => ($order->end_at) ? strtotime($order->end_at) : null,
            'photo_count' => $order->photo_count,
            'links' => [
                [
                    'rel' => 'self',
                    'uri' => route('api.v1.orders.show', ['orders'=>$order->id])
                ]
            ],
        ];
    }

    public function includeService(Order $order)
    {
        $service = $order->service;
        return $this->item($service, new ServiceTransformer);
    }

    public function includeWorker(Order $order)
    {
        $worker = $order->worker;
        if(!$worker) $worker = new \App\User;
        return $this->item($worker, new UserTransformer);
    }

    public function includeVehicle(Order $order)
    {
        $vehicle = $order->vehicle;
        return $this->item($vehicle, new VehicleTransformer);
    }
}
