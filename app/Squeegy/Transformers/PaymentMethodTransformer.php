<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 1/27/16
 * Time: 17:06
 */

namespace app\Squeegy\Transformers;


use App\PaymentMethod;

class PaymentMethodTransformer
{

    public function transform(PaymentMethod $paymentMethod) {

        return [
            'identifier'=>$paymentMethod->identifier,
            'card_type'=>$paymentMethod->card_type,
            'last4'=>$paymentMethod->last4,
            'exp'=>str_pad($paymentMethod->exp_month,2,'0',STR_PAD_LEFT)."/".substr($paymentMethod->exp_year,-2),
            'is_default'=>(bool)$paymentMethod->is_default,
        ];

    }

}