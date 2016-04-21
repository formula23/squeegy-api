<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/26/15
 * Time: 11:07
 */

namespace App\Squeegy;

use Illuminate\Support\Facades\Log;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\Charge as StripeCharge;
use Stripe\Customer as StripeCustomer;

class Payments {

    protected $customer_id;

    protected $amt;

    public function __construct($customer_id)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $this->customer_id = $customer_id;
    }

    public function auth($amt=0, $order=null, $capture=false)
    {
        if($amt===0) return;

        try {
            $charge_data = [
                'amount' => $amt,
                'currency' => 'usd',
                'customer' => $this->customer_id,
                'capture' => $capture,
            ];

            if($order) {
                $charge_data['statement_descriptor'] = substr(trans('messages.order.statement_descriptor', ['job_number'=>$order->job_number]), 0, 20);
            }

            $charge = StripeCharge::create($charge_data);
        } catch(\Exception $e) {
            \Bugsnag::notifyException($e);
            throw new \Exception(trans('messages.order.invalid_card'), 400);
        }


        return $charge;
    }

    public function sale($amt=0, $order=null)
    {
        return $this->auth($amt, $order, true);
    }

    public function capture($charge_id, $amt=0)
    {
        if(!$charge_id) {
            throw new \Exception('No charge_id supplied');
        }

        try {
            $params=[];
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

        $charge = StripeCharge::retrieve($charge_id);

        $params=[];
        if($amt) $params['amount'] = $amt;

        $re = $charge->refund($params);

        return $re;
    }

    public function cards()
    {
        $stripe_customer = StripeCustomer::retrieve($this->customer_id);

        if($stripe_customer->sources) {
            return $stripe_customer->sources->data;
        }
        return [];
    }

    public function card_charged($charge_id)
    {
        $charge = StripeCharge::retrieve($charge_id);
        return $charge->source;
    }

}