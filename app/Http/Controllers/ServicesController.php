<?php namespace App\Http\Controllers;


use App\EtaLog;
use App\Http\Requests;
use App\Squeegy\Orders;
use App\Squeegy\Partners;
use App\Squeegy\Transformers\ServiceAvailabilityTransformer;
use App\Squeegy\Transformers\ServiceTransformer;
use App\Squeegy\Transformers\ServiceCoordTransformer;
use App\Service;
use App\ServiceCoord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Log;
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
        $services = Service::getAvailableServices();
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

    /**
     * @param Request $request
     * @return mixed
     */
    public function availability(Request $request)
    {
        $availability = Orders::availability($request->input('lat'), $request->input('lng'));

        try {
            $etalog_data = [
                'eta' => $availability["time"],
                'city' => Orders::$city,
                'state' => Orders::$state,
                'postal_code' => Orders::$postal_code,
                'latitude' => Orders::$lat,
                'longitude' => Orders::$lng,
                'message' => $availability["code"],
                'ip_address' => $request->getClientIp(),
            ];
            if(Auth::user()) {
                $etalog_data['user_id'] = Auth::user()->id;
            }
            EtaLog::create($etalog_data);

        } catch(\Exception $e) {
            \Bugsnag::notifyException($e);
        }

        Log::info("services@availability");
        Log::info($availability);

        return $this->response->withItem($availability, new ServiceAvailabilityTransformer());
    }
}
