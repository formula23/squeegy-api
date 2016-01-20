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
            'worker_id' => (!empty($data['worker_id']) ? $data['worker_id'] : 0) ,
            'postal_code' => $data['postal_code'],
            'service_area' => $data['service_area'],
        ];

    }

    public function includeServices()
    {
        $services = Service::all();
        return $this->collection($services, new ServiceTransformer);
    }

}