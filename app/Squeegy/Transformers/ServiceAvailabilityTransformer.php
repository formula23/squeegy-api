<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/17/15
 * Time: 14:03
 */

namespace App\Squeegy\Transformers;

use App\Service;
use League\Fractal\TransformerAbstract;

class ServiceAvailabilityTransformer extends TransformerAbstract {

    protected $defaultIncludes = [
        'services'
    ];

    public function transform(array $data) {

        return [
            'accept' => (int)$data['accept'],
            'description' => $data['description'],
            'time' => $data['time'],
            'time_label' => $data['time_label'],
        ];

    }

    public function includeServices()
    {
        $services = Service::all();
        return $this->collection($services, new ServiceTransformer);
    }

}