<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 11/26/15
 * Time: 02:18
 */

namespace App\Squeegy\Transformers;

use App\WasherActivityLog;
use League\Fractal\TransformerAbstract;

class WasherActivityLogTransformer extends TransformerAbstract {

    public function transform(WasherActivityLog $washerActivityLog)
    {
        return [
            'log_on' => $washerActivityLog->log_on ? (object) ["date"=>$washerActivityLog->log_on->format("Y-m-d H:i:s")] : null,
            'log_off' => $washerActivityLog->log_off ? (object) ["date"=>$washerActivityLog->log_off->format("Y-m-d H:i:s")] : null,
        ];
    }


}