<?php namespace App\Http\Controllers;

use App\Http\Requests;

use App\OctaneLA\Transformers\ServiceTransformer;
use App\Service;
use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;
use Illuminate\Http\Request;

/**
 * Class ServicesController
 * @package App\Http\Controllers
 */
class ServicesController extends ApiGuardController {

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

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

}