<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/28/16
 * Time: 23:28
 */

namespace app\Squeegy\Transformers;


use App\CancelReason;
use App\OrderSchedule;
use League\Fractal\TransformerAbstract;

class CancelReasonTransformer extends TransformerAbstract
{

    public function transform(CancelReason $cancelReason)
    {
        return [
            'id' => $cancelReason->id,
            'description' => $cancelReason->description,
        ];
    }
    
}