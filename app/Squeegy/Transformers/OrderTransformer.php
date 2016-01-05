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
//        $eta = Orders::getLeadTime($order->location['lat'], $order->location['lon']);

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
            'eta_quote' => (int)$order->eta,
            'arrival_eta' => eta_real_time($order),
            'eta' => Orders::formatConfirmEta($order->eta),
//            'eta' => 'Around '.eta_real_time($order),
            'eta_seconds' => Orders::getCurrentEta($order),
            'etc' => ($order->start_at ? $order->start_at->addMinutes($order->etc)->format('g:i a') : ""),
            'completed_time' => ($order->done_at) ? strtotime($order->done_at) : null,
            'photo_count' => $order->photo_count,
            'rating' => $order->rating,
            'confirm_time' => $order->confirm_at ? date("g:i:s a", strtotime($order->confirm_at)) : "",
            'enroute_time' => $order->enroute_at ? date("g:i:s a", strtotime($order->enroute_at)) : "",
            'start_time' => $order->start_at ? date("g:i:s a", strtotime($order->start_at)) : "",
            'done_time' => $order->done_at ? date("g:i:s a", strtotime($order->done_at)) : "",
            'confirm_at' => $order->confirm_at,
            'enroute_at' => $order->enroute_at,
            'start_at' => $order->start_at,
            'done_at' => $order->done_at,
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
