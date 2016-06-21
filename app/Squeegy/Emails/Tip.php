<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/4/16
 * Time: 01:20
 */

namespace App\Squeegy\Emails;

use Carbon\Carbon;

class Tip extends Email
{
    /**
     * @return mixed
     */
    protected function getEmailId()
    {
        return config("campaignmonitor.template_ids.tip");
    }

    public function variables($user, $order)
    {
        $vars=[
            'CURRENT_YEAR' => Carbon::now()->year,
            'ORDER_NUMBER' => $order->job_number,
            'ORDER_DATE' => $order->done_at->format('m/d/Y'),
            'ORDER_TIME' => $order->done_at->format('g:ia'),
            'VEHICLE' => $order->vehicle->full_name(),
            'LICENSE_PLATE' => ($order->vehicle->license_plate?:null),
            'WASHER_NAME'=>$order->worker->name,
            'TIP' => '$'.number_format($order->tip/100, 2),
            'VEHICLE_PIC' => config('squeegy.emails.receipt.photo_url').$order->id.'.jpg',
            'WASHER_PIC' => config('squeegy.emails.tip.washer_url').$order->worker_id.'.png',
        ];

        return $vars;
    }
}