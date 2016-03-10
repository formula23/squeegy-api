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
            'log_on' => $washerActivityLog->log_on,
            'log_off' => $washerActivityLog->log_off,
        ];
    }


}