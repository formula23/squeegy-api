<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 12/23/15
 * Time: 20:46
 */

namespace App\Squeegy\Transformers;

use App\WasherActivityLog;
use App\WasherLocation;
use League\Fractal\TransformerAbstract;

class WasherLocationTransformer extends TransformerAbstract {

    protected $defaultIncludes = [
        'washer',
        'activity_log',
    ];

    public function transform(WasherLocation $washerLocation)
    {
        return [
            'latitude' => $washerLocation->latitude,
            'longitude' => $washerLocation->longitude,
        ];
    }

    public function includeActivityLog(WasherLocation $washerLocation)
    {
        $washer_log = new WasherActivityLog();
        $washer_log->login = $washerLocation->login;
        $washer_log->logout = $washerLocation->logout;
        $washer_log->log_on = $washerLocation->log_on;
        $washer_log->log_off = $washerLocation->log_off;
        return $this->item($washer_log, new WasherActivityLogTransformer());
    }

    public function includeWasher(WasherLocation $washerLocation)
    {
        return $this->item($washerLocation->user, new UserTransformer());
    }

}