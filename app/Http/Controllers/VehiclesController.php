<?php namespace App\Http\Controllers;

use App\OctaneLA\Transformers\VehicleTransformer;
use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;
use Response;
use App\Vehicle;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VehiclesController extends ApiGuardController {


    /**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
        $vehicles = Vehicle::all();

        return $this->response->withCollection($vehicles, new VehicleTransformer, 'vehicles');

	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
	public function store(Request $request)
	{
        if( ! $request->input('year') &&
            ! $request->input('make') &&
            ! $request->input('color'))
        {
            return $this->response->errorWrongArgs('Arguments missing');
        }

        $data = $request->all();
        $data['user_id'] = 1;

        $vehicle = Vehicle::create($data);

        return $this->response->withItem($vehicle, new VehicleTransformer);

    }

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$vehicle = Vehicle::find($id);

        if ( ! $vehicle)
        {
            return $this->response->errorNotFound('Vehicle does not exist');

        }

        return $this->response->withItem($vehicle, new VehicleTransformer);

	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}


}
