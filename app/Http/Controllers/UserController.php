<?php namespace App\Http\Controllers;

use Aloha\Twilio\Twilio;
use App\Events\UserUpdated;
use App\Http\Requests;
use App\PaymentMethod;
use App\Squeegy\Transformers\OrderTransformer;
use App\Squeegy\Transformers\UserTransformer;
use App\User;
use App\WasherActivityLog;
use Carbon\Carbon;
use Guzzle\Service\Exception\ValidationException;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateUserRequest;
use Aws\Sns\SnsClient;
use App\Events\UserRegistered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Customer as StripeCustomer;
use Exception;

class UserController extends Controller {

    protected $limit = 100;

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
//        $this->middleware('user_has_access', ['only'=>['show','update']]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory
     */
    public function index(Request $request)
    {
        $type = $request->input('type');
        if(!$type) $type = "customers";

        $usr_qry = User::$type()->where('email','not like','%squeegyapp-tmp.com');

        if($type=="workers") { //get activity log
            $usr_qry->leftJoin(\DB::raw("(select user_id, login, logout, log_on, log_off from washer_activity_logs where logout is null order by log_on desc) as wal"), function($q) {
                $q->on('users.id', '=', 'wal.user_id');
            })->groupBy('users.id');
        }

        if($request->input('name')) {
            $usr_qry->where('name', 'like', '%'.$request->input('name')."%");
        }

        if($request->input('email')) {
            $usr_qry->where('email', 'like', '%'.$request->input('email')."%");
        }

        if($request->input('is_active')) {
            $usr_qry->where('is_active', $request->input('is_active'));
        }

        if($request->input('referral_code')) {
            $usr_qry->where('referral_code', $request->input('referral_code'));
        }

        if($request->input('limit')) {
            if((int)$request->input('limit') < 1) $this->limit = 1;
            else $this->limit = $request->input('limit');
        }

        $paginator = $usr_qry->paginate($request->input('per_page', $this->limit));

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
     * @param null $id
     * @param UpdateUserRequest $request
     * @param SnsClient $sns_client
     * @param Twilio $twilio
     * @return Response
     */
	public function update(UpdateUserRequest $request, SnsClient $sns_client, Twilio $twilio, $id=0)
	{
        $data = $request->all();

        $user = ( $id ? User::find($id) : $request->user() );

        if($user->is('worker') && !empty($data['zone_id'])) {
            if($data['attach']=='yes') {
                $user->zones()->attach($data['zone_id']);
            } else {
                $user->zones()->detach($data['zone_id']);
            }
        }

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

        if( ! $user->stripe_customer_id) {
            $customer = StripeCustomer::create([
                "description" => (isset($data["name"]) ? $data["name"] : $user->name ),
                "email" => (isset($data["email"]) ? $data["email"] : $user->email ),
            ]);
            $data['stripe_customer_id'] = $customer->id;

        } else {
            $customer = StripeCustomer::retrieve($user->stripe_customer_id);
            if( ! empty($data['email'])) $customer->email = $data['email'];
            if( ! empty($data['name'])) $customer->description = $data['name'];
        }

        /**
         * stripe_token passed, add card to customer
         */
        if( ! empty($data['stripe_token'])) {
            try {
                $customer_card = $customer->sources->create(["source" => $data['stripe_token']]);
                $customer->default_source = $customer_card->id;
            } catch(\Exception $e) {
                \Bugsnag::notifyException($e);
                if( ! ($e instanceof \Stripe\Error\InvalidRequest) && ! str_contains($e->getMessage(), 'Stripe token more than once')) {
                    return $this->response->errorWrongArgs('Credit Card Error: '.$e->getMessage());
                }
            }
        }

        try {
            $customer->save();
        } catch (\Exception $e) {
            \Bugsnag::notifyException($e);
        }

// && ! empty($user->phone) -- removed 9/17

        if( ! empty($data["phone"])) {
            if($data["phone"] != preg_replace("/^\+1/","",$user->phone)) {
                try {
                    $twilio->message($data["phone"], trans('messages.profile.phone_verify', ['verify_code'=>config('squeegy.sms_verification')]));
                } catch(\Services_Twilio_RestException $e) {
                    return $this->response->errorWrongArgs("Please enter a valid phone number.");
                }
            }
        }

        if( ! empty($data['password']) || ! empty($data['facebook_id']) || ! empty($data['facebook_token'])) {
            $data['anon_pw_reset'] = 1;
            $data['tmp_fb'] = 0;
        }

        $original_email = $user->email;

        $user->update($data);

        if( ! empty($data['email']) && preg_match('/squeegyapp-tmp\.com$/', $original_email)) {
            \Event::fire(new UserRegistered($user));
        }

        if( ! preg_match('/squeegyapp-tmp\.com$/', $original_email) &&  (! empty($data['email']) ||  ! empty($data['name']))) {
            \Event::fire(new UserUpdated($original_email, $user));
        }

        return $this->response->withItem($user, new UserTransformer());
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

//        $worker_id = ($request->input('user_id') ? $request->input('user_id') : \Auth::user()->id );
//        WasherActivityLog::where('user_id', $worker_id)->whereNull('log_off')->update(['log_off' => Carbon::now()]);

        switch($request->input('status')) {
            case "on":
                $activity_log = Auth::user()->activity_logs()->whereNull('logout')->orderby('login', 'desc')->first();

                if($activity_log->log_on === null) {
                    $activity_log->update(['log_'.$request->input('status') => Carbon::now()]);
                } else {
                    $copy_activity_log = $activity_log->replicate();
                    $copy_activity_log->log_on = Carbon::now();
                    $copy_activity_log->log_off = null;
                    $copy_activity_log->save();
                }

                break;
            case "off":
                Auth::user()->activity_logs()->whereNull('log_off')->update(['log_off' => Carbon::now()]);
                break;
        }

//        Auth::user()
//        Auth::user()->activity_logs()->whereNull('logout')->update(['log_'.$request->input('status') => Carbon::now()]);
//        switch($request->input('status')) {
//            case "on":
//
////                User::find($worker_id)->activity_logs()->create(['log_on' => Carbon::now()]);
//                break;
//        }

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

    public function latestOrder(Request $request)
    {
        if( ! $request->user()->is('customer')) {
            return $this->response->errorForbidden('Unauthorized.');
        }

        $latestOrder = $request->user()->latestOrder($request->input('partner_id'));

//        if($partner_id = $request->input('partner_id')) {
//            $latestOrder = $request->user()->latestOrder($partner_id)->where('partner_id', $partner_id)->first();
//        }
//
//        if( ! $latestOrder) $latestOrder = $request->user()->pastOrders()->whereNull('partner_id')->first();

        if(is_null($latestOrder)) {
            return $this->response->errorNotFound('No previous orders.');
        }

        return $this->response->withItem($latestOrder, new OrderTransformer());
    }
        
    
}
