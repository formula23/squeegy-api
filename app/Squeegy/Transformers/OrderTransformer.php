<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/12/15
 * Time: 00:25
 */

namespace App\Squeegy\Transformers;

use App\OrderSchedule;
use App\Squeegy\Orders;
use App\Order;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class OrderTransformer extends TransformerAbstract {

    protected $defaultIncludes = [
        'vehicle',
        'service',
        'worker',
        'customer',
        'schedule',
    ];

    protected $availableIncludes = [
        'referrer',
    ];

    public function transform(Order $order)
    {
//        $eta = Orders::getLeadTime($order->location['lat'], $order->location['lon']);

        return [
            'id' => (string)$order->id,
            'job_number' => $order->job_number,
            'status' => $order->status,
            'is_schedule_order' => ($order->schedule?true:false),
            'location' => $order->location,
            'instructions' => $order->instructions,
            'subtotal' => (int)$order->price,
            'discount' => (($order->discount)? $order->discount : null ),
            'credit' => (($order->credit)? $order->credit : null ),
            'total' => (int)($order->price - (int)$order->discount - (int)$order->credit),
            'promo_code' => (($order->promo_code)? $order->promo_code : null ),
            'eta_quote' => (int)$order->eta,
            'arrival_eta' => eta_real_time($order),
            'current_eta' => current_eta($order),
            'eta' => Orders::formatConfirmEta($order->eta),
            'eta_seconds' => Orders::getCurrentEta($order),
            'etc' => ($order->start_at ? $order->start_at->addMinutes($order->etc)->format('g:i a') : ""),
            'completed_time' => ($order->done_at) ? strtotime($order->done_at) : null,
            'photo_count' => $order->photo_count,
            'rating' => $order->rating,
            'confirm_time' => $order->confirm_at ? date("g:i:s a", strtotime($order->confirm_at)) : "",
            'assign_time' => $order->assign_at ? date("g:i:s a", strtotime($order->assign_at)) : "",
            'enroute_time' => $order->enroute_at ? date("g:i:s a", strtotime($order->enroute_at)) : "",
            'start_time' => $order->start_at ? date("g:i:s a", strtotime($order->start_at)) : "",
            'done_time' => $order->done_at ? date("g:i:s a", strtotime($order->done_at)) : "",
            'confirm_at' => $order->confirm_at,
            'schedule_at' => $order->schedule_at,
            'assign_at' => $order->assign_at,
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

    public function includeSchedule(Order $order)
    {
        $schedule = ($order->schedule?:new OrderSchedule());
        return $this->item($schedule, new OrderScheduleTransformer);
    }

    public function includeReferrer(Order $order) {
        return $this->item($order->referrer, new UserTransformer);
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
