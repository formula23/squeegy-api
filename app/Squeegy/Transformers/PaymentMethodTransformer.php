<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 1/27/16
 * Time: 17:06
 */

namespace app\Squeegy\Transformers;


use App\PaymentMethod;
use Stripe\Card;

class PaymentMethodTransformer
{

    public function transform($card) {

        if( ! $card) return [];

        $data = [
            'brand'=>$card->brand,
            'last4'=>$card->last4,
        ];

        if($card->exp_month && $card->exp_year) {
            $data['exp'] = str_pad($card->exp_month,2,'0',STR_PAD_LEFT)."/".substr($card->exp_year,-2);
        }

        return $data;

    }

}