<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\OctaneLA\Transformers\UserTransformer;
use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;
use Guzzle\Service\Exception\ValidationException;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateUserRequest;
use Aws\Sns\SnsClient;
use Stripe\Error\InvalidRequest;
use Stripe\Stripe;
use Stripe\Customer as StripeCustomer;

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

        if(isset($data['push_token'])) {

            try {
                $endpoint_arn = $sns_client->createPlatformEndpoint([
                    'PlatformApplicationArn' => \Config::get('aws.sns_arn'),
                    'Token' => $data['push_token'],
                ]);

                if( ! $endpoint_arn->get('EndpointArn')) {
                    return $this->response->errorInternalError('Unable to create push token');
                }
                $data['push_token'] = $endpoint_arn->get('EndpointArn');
            } catch (ValidationException $e) {
                return $this->response->errorInternalError($e->getMessage());
            } catch (\ErrorException $e) {
                return $this->response->errorInternalError($e->getMessage());
            }

        }

        if(isset($data['stripe_token'])) {

            try {
                Stripe::setApiKey(\Config::get('stripe.api_key'));
                $customer = StripeCustomer::retrieve($request->user()->stripe_customer_id);
                $customer_card = $customer->sources->create([
                    "source" => $data['stripe_token']
                ]);

                $customer->default_source = $customer_card->id;
                $customer->save();

            } catch (InvalidRequest $e) {
                return $this->response->errorInternalError($e->getMessage());
            } catch (\ErrorException $e) {
                return $this->response->errorInternalError($e->getMessage());
            }
        }

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
