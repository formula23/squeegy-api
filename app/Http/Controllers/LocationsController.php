<?php namespace App\Http\Controllers;

use App\Http\Requests;
use Auth;
use App\OctaneLA\Transformers\LocationTransformer;
use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;
use App\Location;

use App\Http\Requests\LocationRequest;

/**
 * Class LocationsController
 * @package App\Http\Controllers
 */
class LocationsController extends ApiGuardController {

    /**
     *
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
        $locations = Auth::user()->locations;

        return $this->response->withCollection($locations, new LocationTransformer);
	}

    /**
     * Store a newly created resource in storage.
     *
     * @param LocationRequest $request
     * @param Location $location
     * @return Response
     */
	public function store(LocationRequest $request, Location $location)
	{
        $location = new Location($request->all());

        Auth::user()->locations()->save($location);

        return $this->response->withItem($location, new LocationTransformer);
	}

    /**
     * Display the specified resource.
     *
     * @param Location $location
     * @return Response
     */
	public function show(Location $location)
	{
        if (empty($location->id)) {
            return $this->response->errorNotFound('Location does not exist');
        }

        return $this->response->withItem($location, new LocationTransformer);
	}


    /**
     * Update the specified resource in storage.
     *
     * @param LocationRequest $request
     * @param Location $location
     * @return Response
     */
	public function update(LocationRequest $request, Location $location)
	{
        if (empty($location->id)) {
            return $this->response->errorNotFound('Location does not exist');
        }

        $location->update($request->all());

        return $this->response->withItem($location, new LocationTransformer);
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param Location $location
     * @return Response
     * @throws \Exception
     */
	public function destroy(Location $location)
	{
        if (empty($location->id)) {
            return $this->response->errorNotFound('Location does not exist');
        }

        $location->delete();

        return response()->json(['success'=>'1']);
	}

}
