<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 12/23/15
 * Time: 20:46
 */

namespace App\Squeegy\Transformers;

use App\WasherLocation;
use League\Fractal\TransformerAbstract;

class WasherLocationTransformer extends TransformerAbstract {

    protected $defaultIncludes = [
        'washer',
    ];

    public function transform(WasherLocation $washerLocation)
    {
        return [
            'latitude' => $washerLocation->latitude,
            'longitude' => $washerLocation->longitude,
        ];
    }

    public function includeWasher(WasherLocation $washerLocation)
    {
        return $this->item($washerLocation->user, new UserTransformer());
    }

}