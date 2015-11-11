<?php namespace App\Http\Controllers;

use Aloha\Twilio\Twilio;
use App\Http\Requests;
use App\Squeegy\Transformers\UserTransformer;
use App\User;
use App\WasherActivityLog;
use Carbon\Carbon;
use Guzzle\Service\Exception\ValidationException;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateUserRequest;
use Aws\Sns\SnsClient;
use App\Events\UserRegistered;
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

            try {
                $customer_card = $customer->sources->create([
                    "source" => $data['stripe_token']
                ]);
                $customer->default_source = $customer_card->id;

            } catch(\Exception $e) {
                \Bugsnag::notifyException($e);
//                return $this->response->errorWrongArgs($e->getMessage());
            }

        }

        try {
            $customer->save();
        }
        catch (\Exception $e)
        {
            dd($e);
        }


// && ! empty($request->user()->phone) -- removed 9/17

        if( ! empty($data["phone"])) {

            if($data["phone"] != preg_replace("/^\+1/","",$request->user()->phone)) {
                try {
                    $twilio->message($data["phone"], trans('messages.profile.phone_verify', ['verify_code'=>config('squeegy.sms_verification')]));
                } catch(\Services_Twilio_RestException $e) {
                    return $this->response->errorWrongArgs("Please enter a valid phone number.");
                }
            }

        }

        $original_email = $request->user()->email;

        $request->user()->update($data);

        if( ! empty($data['email']) && preg_match('/squeegyapp-tmp\.com$/', $original_email)) {
            \Event::fire(new UserRegistered());
        }

        return $this->response->withItem($request->user(), new UserTransformer());
	}

    public function phoneVerify(Twilio $twilio)
    {
        try {
            $twilio->message(\Auth::user()->phone, trans('messages.profile.phone_verify', ['verify_code' => config('squeegy.sms_verification')]));
        } catch (\Services_Twilio_RestException $e) {
            \Bugsnag::notifyException(new \Exception($e->getMessage()));
            return $this->response->errorWrongArgs("Unable to send SMS.");
        }

        return $this->response->withItem(\Auth::user(), new UserTransformer());
    }

    public function duty(Request $request)
    {
        if( ! \Auth::user()->can('set.duty')) {
            return $this->response->errorUnauthorized();
        }

        if(\Auth::user()->is('admin') && !$request->input('user_id')) {
            return $this->response->withArray(['message'=>'user_id required']);
        }

        $worker_id = ($request->input('user_id') ? $request->input('user_id') : \Auth::user()->id );

        WasherActivityLog::where('user_id', $worker_id)->whereNull('log_off')->update(['log_off' => Carbon::now()]);

        switch($request->input('status')) {
            case "on":
                User::find($worker_id)->activity_logs()->create(['log_on' => Carbon::now()]);
                break;
        }

        return $this->response->withArray(['success'=>1]);

    }

}
