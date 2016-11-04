<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 9/21/16
 * Time: 16:04
 */

namespace app\Squeegy\Transformers;


use League\Fractal\TransformerAbstract;

class PartnerAvailableDaysTransformer extends TransformerAbstract
{

    public function transform($availability)
    {
        return [
            "day" => $availability['day'],
            "time_slots" => $availability['time_slots'],
        ];
    }

}