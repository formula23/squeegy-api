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

    protected $defaultIncludes = [
        'vehicle',
        'service'
    ];

    protected $availableIncludes = [
        'washer',
    ];

    public function transform(Order $order)
    {
        return [
            'job_number' => $order->job_number,
            'status' => $order->status,
            'location' => $order->location,
            'instructions' => $order->instructions,
            'price' => $order->price,
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

    public function includeWasher(Order $order)
    {
        $washer = $order->washer;
        if(!$washer) $washer = new \App\Washer;
        return $this->item($washer, new WasherTransformer);
    }

    public function includeVehicle(Order $order)
    {
        $vehicle = $order->vehicle;
        return $this->item($vehicle, new VehicleTransformer);
    }
}
