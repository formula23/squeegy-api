<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\OctaneLA\Orders;
use App\OctaneLA\Transformers\ServiceAvailabilityTransformer;
use App\OctaneLA\Transformers\ServiceTransformer;
use App\OctaneLA\Transformers\ServiceCoordTransformer;
use App\Service;
use App\ServiceCoord;
use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;
use Request;
use Carbon\Carbon;

/**
 * Class ServicesController
 * @package App\Http\Controllers
 */
class ServicesController extends ApiGuardController {

    /**
     * Get all services
     */
    public function index()
    {
        $services = Service::all();

        return $this->response->withCollection($services, new ServiceTransformer);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @param Service $service
     * @return Response
     */
	public function show(Service $service)
	{
        if(empty($service->id)) {
            return $this->response->errorNotFound('Service not found');
        }

        return $this->response->withItem($service, new ServiceTransformer);
	}

    /**
     * @return mixed
     */
    public function coords()
    {
        $service_coords = ServiceCoord::all();

        return $this->response->withCollection($service_coords, new ServiceCoordTransformer);
    }

    public function availability()
    {
        $data = ['accept'=>Orders::open(), 'description'=>'', 'time'=>0, 'time_label'=>''];

        if( ! Orders::open()) $data['description'] = 'You have reached us after hours.';

        $lead_time = Orders::getLeadTime();
        if( ! $lead_time) {
            $data['accept'] = 0;
            $data['description'] = 'Due to high-demand, we cannot take your request.';
        }

        $lead_time_arr = Orders::formatLeadTime($lead_time);

        $data = array_merge($data, $lead_time_arr);

        return $this->response->withItem($data, new ServiceAvailabilityTransformer);
    }


}
