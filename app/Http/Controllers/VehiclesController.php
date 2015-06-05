<?php namespace App\Http\Controllers;

use App\OctaneLA\Transformers\VehicleTransformer;
use Response;
use App\Vehicle;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VehiclesController extends ApiController {

    /**
     * @var App\OctaneLA\Transformers\VehicleTransformer
     */
    protected $vehicleTransformer;

    function __construct(VehicleTransformer $vehicleTransformer)
    {
        $this->vehicleTransformer = $vehicleTransformer;

//        $this->middleware('auth.basic');

    }


    /**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
        $vehicles = Vehicle::all();

        return $this->respond([
            'data' => $this->vehicleTransformer->transformCollection($vehicles->all())
        ]);

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
	 * @return Response
	 */
	public function store(Request $request)
	{
        if( ! $request->input('year') &&
            ! $request->input('make') &&
            ! $request->input('color'))
        {
            return $this->respondValidationError("Required parameters missing!");
        }

        $data = $request->all();
        $data['user_id'] = 1;

        $vehicle = Vehicle::create($data);

        return $this->respondCreated($vehicle, 'Vehicle Created');

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
            return $this->respondNotFound('Vehicle does not exist');

        }

        return $this->respond([
            'data' => $this->vehicleTransformer->transform($vehicle)
        ]);

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
