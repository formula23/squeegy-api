<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/12/15
 * Time: 00:25
 */

namespace App\Squeegy\Transformers;

use App\Squeegy\Orders;
use App\Order;
use League\Fractal\TransformerAbstract;

class OrderTransformer extends TransformerAbstract {

    protected $defaultIncludes = [
        'vehicle',
        'service',
        'worker',
        'customer',
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
            'promo_code' => (($order->promo_code)? $order->promo_code : null ),
            'total' => (int)($order->price - (int)$order->discount),
            'eta' => Orders::formatConfirmEta(Orders::getLeadTime($order)),
            'eta_seconds' => Orders::getCurrentEta($order),
            'completed_time' => ($order->done_at) ? strtotime($order->done_at) : null,
            'photo_count' => $order->photo_count,
            'links' => [
                [
                    'rel' => 'self',
                    'uri' => route('v1.orders.show', ['orders'=>$order->id])
                ]
            ],
        ];
    }

    public function includeCustomer(Order $order) {
        return $this->item($order->customer, new UserTransformer);
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
