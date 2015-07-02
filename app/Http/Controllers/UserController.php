<?php namespace App\Http\Controllers;

use Aloha\Twilio\Twilio;
use App\Http\Requests;
use App\Squeegy\Transformers\UserTransformer;
use Guzzle\Service\Exception\ValidationException;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateUserRequest;
use Aws\Sns\SnsClient;
use Stripe\Stripe;
use Stripe\Customer as StripeCustomer;
use Exception;

class UserController extends Controller {

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
        return $this->response->withItem($request->user(), new UserTransformer);
	}

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUserRequest $request
     * @param SnsClient $sns_client
     * @return Response
     */
	public function update(UpdateUserRequest $request, SnsClient $sns_client, Twilio $twilio)
	{
        $data = $request->all();

        if(isset($data['push_token'])) {

            try {
                $endpoint_arn = $sns_client->createPlatformEndpoint([
                    'PlatformApplicationArn' => \Config::get('aws.sns_arn'),
                    'Token' => $data['push_token'],
                ]);

                if (!$endpoint_arn->get('EndpointArn')) {
                    return $this->response->errorInternalError('Unable to create push token');
                }
                $data['push_token'] = $endpoint_arn->get('EndpointArn');
            } catch (ValidationException $e) {
                return $this->response->errorWrongArgs($e->getMessage());
            } catch (Exception $e) {
                return $this->response->errorInternalError($e->getMessage());
            }
        }

        /**
         * Create or Update Stripe Customer Object
         */

        Stripe::setApiKey(\Config::get('services.stripe.secret'));

        if( ! $request->user()->stripe_customer_id) {

            $customer = StripeCustomer::create([
                "description" => (isset($data["name"]) ? $data["name"] : $request->user()->name ),
                "email" => (isset($data["email"]) ? $data["email"] : $request->user()->email ),
            ]);
            $data['stripe_customer_id'] = $customer->id;

        } else {
            $customer = StripeCustomer::retrieve($request->user()->stripe_customer_id);
            if( ! empty($data['email'])) $customer->email = $data['email'];
            if( ! empty($data['name'])) $customer->description = $data['name'];
        }

        /**
         * stripe_token passed, add card to customer
         */
        if( ! empty($data['stripe_token'])) {

            $customer_card = $customer->sources->create([
                "source" => $data['stripe_token']
            ]);

            $customer->default_source = $customer_card->id;
        }

        $customer->save();

        if( ! empty($data["phone"])) {

            if($data["phone"] != preg_replace("/^\+1/","",$request->user()->phone)) {
                $twilio->message($data["phone"], trans('messages.profile.phone_verify', ['verify_code'=>config('squeegy.sms_verification')]));
            }

        }

        $request->user()->update($data);

        return $this->response->withItem($request->user(), new UserTransformer());
	}

}
