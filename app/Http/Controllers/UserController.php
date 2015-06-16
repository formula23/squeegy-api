<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\OctaneLA\Transformers\UserTransformer;
use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateUserRequest;

class UserController extends ApiGuardController {

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return Response
     */
	public function show(Request $request)
	{
        return $this->response->withItem($request->user(), new UserTransformer());
	}

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUserRequest $request
     * @return Response
     */
	public function update(UpdateUserRequest $request)
	{
        $request->user()->update($request->all());

        return $this->response->withItem($request->user(), new UserTransformer());
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @return Response
	 */
	public function destroy()
	{
		//
	}

}
