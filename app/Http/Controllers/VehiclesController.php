<?php namespace App\Http\Controllers;

use Aloha\Twilio\Twilio;
use App\OctaneLA\Transformers\VehicleTransformer;
use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;
use Illuminate\Support\Facades\Auth;
use Response;
use App\Vehicle;
use App\Http\Requests;
use App\Http\Requests\CreateVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;

/**
 * Class VehiclesController
 * @package App\Http\Controllers
 */
class VehiclesController extends ApiGuardController {

    /**
     * Display a listing of the resource.
     *
     * @param Twilio $twilio
     * @return Response
     */
	public function index(Twilio $twilio)
	{

        $twilio->message('+13106004938', '');

        dd($twilio);

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
        if (empty($vehicle->id)) {
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
        if (empty($vehicle->id)) {
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
        if (empty($vehicle->id)) {
            return $this->response->errorNotFound('Vehicle does not exist');
        }

        $vehicle->delete();

        return response()->json(['success'=>'1']);
	}

}
