<?php
/**
 * Created by PhpStorm.
 * User: danschultz
 * Date: 6/17/15
 * Time: 14:03
 */

namespace App\Squeegy\Transformers;

use App\Service;
use App\Partner;
use App\Squeegy\Schedule;
use League\Fractal\TransformerAbstract;

class ServiceAvailabilityTransformer extends TransformerAbstract {

    protected $defaultIncludes = [
        'services'
    ];

    public function transform(array $data)
    {
        $resp = [
            'accept' => (int)$data['accept'],
            'schedule' => $data['schedule'],
            'description' => $data['description'],
            'time' => $data['time'],
            'actual_time' => @$data['actual_time'],
            'time_label' => $data['time_label'],
            'worker_id' => ( ! empty($data['worker_id']) ? $data['worker_id'] : 0 ),
            'postal_code' => $data['postal_code'],
            'service_area' => $data['service_area'],
        ];

        $availability = (new Schedule($data['postal_code'], $data['partner_id']))->availability();
        $resp['available_schedule'] = (isset($availability['available_days']) ? $availability['available_days'] : $availability);

        return $resp;
    }

    public function includeServices()
    {
        $services = Service::getAvailableServices();
        
        if($partner = Partner::where_coords_in(\Request::input('lat'), \Request::input('lng'))) {
            $services = $partner->services;
        }
       
        return $this->collection($services, new ServiceTransformer());
    }

}