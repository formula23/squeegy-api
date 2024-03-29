<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 4/13/16
 * Time: 17:22
 */

namespace App\Squeegy\Emails;


use Carbon\Carbon;

class Receipt extends Email
{

    public function getEmailId()
    {
        return config("campaignmonitor.template_ids.receipt");
    }

    public function variables($user, $order)
    {
        $vehicle =& $order->vehicle;
        $location = $order->location;

        $order_details = $order->order_details->toArray();
        array_walk( $order_details, function(&$val) {
            $val['amount'] = "$".number_format($val['amount']/100, 2);
        });

        $charges = $order->charges()->toArray();
        array_walk($charges, function(&$val) {
            $val['amount'] = "$".number_format($val['amount']/100, 2);
        });

        $vars = [
            'ORDER_ID' => $order->id,
            'ORDER_DATE' => $order->done_at->format('m/d/Y'),
            'ORDER_TIME' => $order->done_at->format('g:ia'),
            'ORDER_NUMBER' => $order->job_number,
            'SERVICE' => $order->service->name.' Wash',
            'SERVICE_PRICE' => number_format($order->price/100, 2),
            'SUBTOTAL' => number_format($order->price/100, 2),
            'PROMO' => ($order->promo_code?:null),
            'DISCOUNT_AMOUNT' => number_format($order->discount/100, 2),
            'CHARGED' => number_format($order->charged/100, 2),
            'SUBSCRIPTION' => ($order->isSubscription() ? number_format($order->total/100, 2) : null ),
            'TOTAL' => number_format($order->total/100, 2),
            'CREDIT_AMOUNT' => number_format($order->credit/100, 2),

            'CUSTOMER_NAME' => $user->name,
            'VEHICLE' => $vehicle->year." ".$vehicle->make." ".$vehicle->model." (".$vehicle->color.")",
            'VEHICLE_PIC' => config('squeegy.emails.receipt.photo_url').$order->id.'.jpg',
            'LICENSE_PLATE' => ($vehicle->license_plate?:null),
            'ADDRESS' => $location['street'].", ".( ! empty($location['city']) ? $location['city'].", " : "" ).$location['state']." ".$location['zip'],
            'ORDER_DETAILS' => $order_details,
            'SHOW_CHARGE' => (count($charges) ? true : false),
            'CHARGES' => $charges,
            'REFERRAL_CODE' => $user->referral_code,
            'CURRENT_YEAR' => Carbon::now()->year,
            'SUBSCRIPTION' => $order->isSubscription(),
        ];

        if($order->auth_transaction) {
            $vars['CC_TYPE'] = $order->auth_transaction->card_type;
            $vars['CC_LAST4'] = $order->auth_transaction->last_four;
        }
        
        return $vars;
        
    }

}