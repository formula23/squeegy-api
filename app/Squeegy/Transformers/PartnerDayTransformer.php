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
            'day'=>$partnerDay->day,
            'day_of_week'=>$partnerDay->day_of_week,
            'time_start'=>$partnerDay->time_start,
            'time_end'=>$partnerDay->time_end,
        ];
    }
}