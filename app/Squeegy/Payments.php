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

    public function __construct($customer_id)
    {
        Stripe::setApiKey(config('stripe.api_key'));

        $this->customer_id = $customer_id;
    }

    public function charge($amt=0)
    {
        if($amt===0) return;

        $charge = StripeCharge::create([
            "amount" => $amt,
            "currency" => "usd",
            "customer" => $this->customer_id,
        ]);

        return $charge;
    }

}