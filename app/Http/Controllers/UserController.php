<?php namespace App\Http\Controllers;

use Aloha\Twilio\Twilio;
use App\Http\Requests;
use App\PaymentMethod;
use App\Squeegy\Transformers\UserTransformer;
use App\User;
use App\WasherActivityLog;
use App\WasherLocation;
use Carbon\Carbon;
use Guzzle\Service\Exception\ValidationException;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateUserRequest;
use Aws\Sns\SnsClient;
use App\Events\UserRegistered;
use Illuminate\Support\Facades\Config;
use Stripe\Stripe;
use Stripe\Customer as StripeCustomer;
use Exception;

class UserController extends Controller {

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct();

        if($request->header('Authorization')) {
            $this->middleware('jwt.auth');
        } else {
            $this->middleware('auth', ['except' => 'authenticated']);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function index(Request $request)
    {
        $type = $request->input('type');
        if(!$type) $type = "customers";

        $usr_qry = User::$type();

        if($type=="workers") { //get activity log
            $usr_qry->leftJoin(\DB::raw("(select user_id, log_on, log_off from washer_activity_logs where log_off is null) as wal"), function($q) {
                $q->on('users.id', '=', 'wal.user_id');
            })->groupBy('users.id');
        }

        $paginator = $usr_qry->paginate($request->input('per_page', 10));

        return $this->response->withPaginator($paginator, new UserTransformer());
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
	public function show(Request $request, $id = 0)
	{
        if($id) $user = User::find($id);
        else $user = $request->user();

        return $this->response->withItem($user, new UserTransformer());
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

                $config_key = 'aws.sns_arn'.($request->header('X-Device')=="Android" ? '_android' : '');
                $endpoint_arn = $sns_client->createPlatformEndpoint([
                    'PlatformApplicationArn' => \Config::get($config_key),
                    'Token' => $data['push_token'],
                ]);

                if (!$endpoint_arn->get('EndpointArn')) {
                    return $this->response->errorInternalError('Unable to create push token');
                }

                $endpoint_arn = $endpoint_arn->get('EndpointArn');

                if($request->header('X-Device')=="Android") {
                    unset($data['push_token']);
                    $data['target_arn_gcm'] = $endpoint_arn;
                } else {
                    $data['push_token'] = $endpoint_arn;
                }

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

                $payment_method_data = [
                    'identifier'=>$customer_card->id,
                    'card_type'=>$customer_card->brand,
                    'last4'=>$customer_card->last4,
                    'exp_month'=>$customer_card->exp_month,
                    'exp_year'=>$customer_card->exp_year,
                ];

                if($request->user()->payment_methods) {
                    $request->user()->payment_methods()->update($payment_method_data);
                } else {
                    $request->user()->payment_methods()->create($payment_method_data);
                }

                $customer->default_source = $customer_card->id;

            } catch(\Exception $e) {
                \Bugsnag::notifyException($e);
//                return $this->response->errorWrongArgs($e->getMessage());
            }

        }

        try {
            $customer->save();
        } catch (\Exception $e) {
            \Bugsnag::notifyException($e);
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

    public function authenticated()
    {
        return $this->response->withArray(['authenticated'=>\Auth::check()]);
    }

    public function duty(Request $request)
    {
        if( ! \Auth::user()->can('set.duty')) {
            return $this->response->errorUnauthorized();
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

    public function location(Request $request)
    {
        if( ! \Auth::user()->can('set.location')) {
            return $this->response->errorUnauthorized();
        }

        $data = [
            'latitude'=>$request->input('latitude'),
            'longitude'=>$request->input('longitude')
        ];

        \Auth::user()->current_location()->updateOrCreate(['user_id'=>\Auth::user()->id], $data);

        return $this->response->withArray($data);
    }

}
