<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 9/20/16
 * Time: 20:50
 */

namespace app\Squeegy\Transformers;


use League\Fractal\TransformerAbstract;

class PartnerDayAvailabilityTransformer extends TransformerAbstract
{

    public function transform($availability)
    {
        return $availability;
    }

}