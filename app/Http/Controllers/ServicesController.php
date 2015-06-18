<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\OctaneLA\Orders;
use App\OctaneLA\Transformers\ServiceAvailabilityTransformer;
use App\OctaneLA\Transformers\ServiceTransformer;
use App\OctaneLA\Transformers\ServiceCoordTransformer;
use App\Service;
use App\ServiceCoord;
use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;
use Illuminate\Http\Request;
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
        $order = new Orders();

        $time = $order->getLeadTime();
        dd($time);

        $data = [
            'open' => $order->open(),
            'time' => $order->getLeadTime(),
            'time_label' => '',
            'max' => \Config::get('squeegy.operating_hours.max_lead_time'),
        ];

        return $this->response->withItem($data, new ServiceAvailabilityTransformer);
    }


}
