<?php namespace App\Http\Controllers;

use App\Http\Requests\InitOrderRequest;
use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;
use Illuminate\Http\Request;

/**
 * Class OrdersController
 * @package App\Http\Controllers
 */
class OrdersController extends ApiGuardController {


	/**
	 * Store a newly created resource in storage.
	 * @param InitOrderRequest $request
	 * @return Response
	 */
	public function initialize(InitOrderRequest $request)
	{
		dd($request);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

    /**
     * @param $id
     */
    public function enroute($id)
    {

    }

    /**
     * @param $id
     */
    public function start($id)
    {

    }

    /**
     * @param $id
     */
    public function stop($id)
    {

    }
}
