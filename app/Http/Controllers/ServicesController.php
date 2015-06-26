<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Squeegy\Orders;
use App\Squeegy\Transformers\ServiceAvailabilityTransformer;
use App\Squeegy\Transformers\ServiceTransformer;
use App\Squeegy\Transformers\ServiceCoordTransformer;
use App\Service;
use App\ServiceCoord;
use Request;
use Lang;

/**
 * Class ServicesController
 * @package App\Http\Controllers
 */
class ServicesController extends Controller {

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
        $availability = Orders::availability();

//        $data = ['accept'=>Orders::open(), 'description'=>'', 'time'=>0, 'time_label'=>''];
//
//        if( ! Orders::open()) $data['description'] = Lang::get('messages.service.closed');
//
//        $lead_time = Orders::getLeadTime();
//        if( ! $lead_time) {
//            $data['accept'] = 0;
//            $data['description'] = Lang::get('messages.service.highdemand');
//        }
//
//        $lead_time_arr = Orders::formatLeadTime($lead_time);
//
//        $data = array_merge($data, $lead_time_arr);

        return $this->response->withItem($availability, new ServiceAvailabilityTransformer);
    }


}
