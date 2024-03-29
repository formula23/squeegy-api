<?php namespace App\Http\Controllers;

use App\Squeegy\Transformers\VehicleTransformer;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use Response;
use App\Vehicle;
use App\Http\Requests;
use App\Http\Requests\CreateVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;

/**
 * Class VehiclesController
 * @package App\Http\Controllers
 */
class VehiclesController extends Controller {

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct();

        if($request->header('Authorization')) {
            $this->middleware('jwt.auth');
        } else {
            $this->middleware('auth');
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
	public function index()
	{
        $vehicles = Auth::user()->vehicles;

        return $this->response->withCollection($vehicles, new VehicleTransformer);

	}

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateVehicleRequest $request
     * @return Response
     */
	public function store(CreateVehicleRequest $request)
	{
        $vehicle = new Vehicle($request->all());

        Auth::user()->vehicles()->save($vehicle);

        return $this->response->withItem($vehicle, new VehicleTransformer);

    }

    /**
     * Display the specified resource.
     *
     * @param Vehicle $vehicle
     * @return Response
     * @internal param int $id
     */
	public function show(Vehicle $vehicle)
	{
        if (Auth::id() != $vehicle->user_id) {
            return $this->response->errorNotFound('Vehicle does not exist');
        }

        return $this->response->withItem($vehicle, new VehicleTransformer);

	}

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateVehicleRequest $request
     * @param Vehicle $vehicle -- Model route binding in RouteServiceProvider.php
     * @return Response
     */
	public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
	{
        if (Auth::id() != $vehicle->user_id) {
            return $this->response->errorNotFound('Vehicle does not exist');
        }

        $vehicle->update($request->all());

        return $this->response->withItem($vehicle, new VehicleTransformer);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param Vehicle $vehicle
     * @return Response
     * @internal param int $id
     */
	public function destroy(Vehicle $vehicle)
	{
        if (Auth::id() != $vehicle->user_id) {
            return $this->response->errorNotFound('Vehicle does not exist');
        }

        $vehicle->delete();

        return response()->json(['success'=>'1']);
	}

}
