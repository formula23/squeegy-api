<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/12/15
 * Time: 00:25
 */

namespace App\OctaneLA\Transformers;

use App\Service;
use League\Fractal\TransformerAbstract;

class ServiceTransformer extends TransformerAbstract {

    public function transform(Service $service)
    {
        return [
            'name' => $service->name,
            'price' => $service->price,
            'details' => $service->details,
            'time' => $service->time,
            'links' => [
                [
                    'rel' => 'self',
                    'uri' => route('api.v1.services.show', ['service'=>$service->id])
                ]
            ],
        ];
    }
}
