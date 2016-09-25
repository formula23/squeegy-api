<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 5/5/16
 * Time: 23:42
 */

namespace App\Squeegy\Transformers;


use App\PartnerDay;
use League\Fractal\TransformerAbstract;

class PartnerDayTransformer extends TransformerAbstract
{

    public function transform(PartnerDay $partnerDay)
    {
        return [
            'open'=>$partnerDay->open,
            'close'=>$partnerDay->close,
            'cutoff'=>$partnerDay->cutoff,
            'frequency'=>$partnerDay->frequency,
            'order_cap'=>$partnerDay->order_cap,
            'time_slot_frequency'=>$partnerDay->time_slot_frequency,
            'time_slot_cap'=>$partnerDay->time_slot_cap,
            'accepting_orders'=>$partnerDay->accepting_orders,
        ];
    }
}