<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\OctaneLA\Transformers\UserTransformer;
use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateUserRequest;
use Aws\Sns\SnsClient;

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
	public function update(UpdateUserRequest $request, SnsClient $sns_client)
	{
        $data = $request->all();

        $endpoint_arn = $sns_client->createPlatformEndpoint([
            'PlatformApplicationArn' => \Config::get('aws.sns_arn'),
            'Token' => $data['push_token'],
        ]);

        $data['push_token'] = $endpoint_arn->get('EndpointArn');

        $request->user()->update($data);

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
