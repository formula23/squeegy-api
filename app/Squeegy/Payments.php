<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/26/15
 * Time: 11:07
 */

namespace App\Squeegy;

use Stripe\Stripe;
use Stripe\Charge as StripeCharge;

class Payments {

    protected $customer_id;

    protected $amt;

    public function __construct($customer_id)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $this->customer_id = $customer_id;
    }

    public function auth($amt=0)
    {
        if($amt===0) return;

        try {
            $charge = StripeCharge::create([
                'amount' => $amt,
                'currency' => 'usbb',
                'customer' => $this->customer_id,
                'capture' => false,
            ]);
        } catch(\Exception $e) {
            throw new \Exception(trans('messages.order.invalid_card'), 400);
        }


        return $charge;
    }

    public function capture($charge_id, $amt=0)
    {
        if(!$charge_id) {
            throw new \Exception('No charge_id supplied');
        }

        try {
            $params = ['statement_descriptor' => trans('messages.order.statement_descriptor', ['service_level'=>''])];
            if($amt) $params['amount'] = $amt;

            $charge = StripeCharge::retrieve($charge_id);
        } catch(\Exception $e) {
            throw new \Exception(trans('messages.order.invalid_card'), 400);
        }

        return $charge->capture($params);
    }

    public function cancel($charge_id, $amt=0)
    {
        return $this->capture($charge_id, $amt);
    }

    public function refund($charge_id, $amt=0)
    {
        if(!$charge_id) {
            throw new \Exception('No charge_id supplied');
        }

        $params = [];
        if($amt) {
            $params['amount'] = $amt;
        }
        $charge = StripeCharge::retrieve($charge_id);
        return $charge->refund($params);
    }

}