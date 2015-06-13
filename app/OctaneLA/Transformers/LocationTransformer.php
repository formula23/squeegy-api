<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/12/15
 * Time: 00:25
 */

namespace App\OctaneLA\Transformers;

use App\Location;
use League\Fractal\TransformerAbstract;

class LocationTransformer extends TransformerAbstract {

    public function transform(Location $location)
    {
        return [
            'id' => $location->id,
            'address1' => $location->address1,
            'address2' => $location->address2,
            'city' => $location->city,
            'state' => $location->state,
            'zip' => $location->zip,
            'lat' => (float)$location->lat,
            'lng' => (float)$location->lng,
            'links' => [
                [
                    'rel' => 'self',
                    'uri' => route('api.v1.locations.show', ['locations'=>$location->id])
                ]
            ],
        ];
    }
}
