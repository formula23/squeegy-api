<?php namespace App\Http\Controllers\Auth;

use App\Squeegy\Payments;
use App\User;
use Exception;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Http\Request;

use Aloha\Twilio\Twilio;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Customer as StripeCustomer;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Squeegy\Transformers\UserTransformer;

use Bugsnag;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;


/**
 * Class AuthController
 * @package App\Http\Controllers\Auth
 */
class AuthController extends Controller {

    protected $apiMethods = [
        'postLogin' => [
            'logged' => true,
        ],
        'postRegister' => [
            'logged' => true,
        ]
    ];

	/**
	 * Create a new authentication controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
	 * @return void
	 */
	public function __construct(Guard $auth, Registrar $registrar)
	{
        parent::__construct();
		$this->auth = $auth;
		$this->registrar = $registrar;

//        $this->middleware('auth.api');

//		$this->middleware('guest', ['except' => ['postLogin', 'getLogout']]);
//        $this->middleware('auth', ['except' => ['postLogin', 'postRegister']]);
	}

    /**
     * @param Request $request
     * @return mixed
     */
    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email', 'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        //little hack so that our accounts don't need to be active to log into the worker app
        //currently ETA calculations are done by the active state of an account.. so we can't be active.
        if( ! in_array($request->input('email'), ["dan@squeegy.com", "andrew@squeegy.com"])) {
            $credentials['is_active'] = 1;
        }

        if ($this->auth->attempt($credentials, $request->has('remember'))) {
            return $this->response->withItem($this->auth->user(), new UserTransformer())->header('X-Auth-Token', $this->getAuthToken());
        }

        \Bugsnag::notifyException(new \Exception($credentials['email'].' - Unable to login. Attempt to Reset user account. '.$credentials['password']));

        //if login attempt failed -- check to see if the user record based on password is an anon user
        $anon_user_rec = User::where('email', $credentials['password']."@squeegyapp-tmp.com")->get()->first();
        $user_rec = User::where('email', $credentials['email'])->get()->first();

        if( ! preg_match('/squeegyapp-tmp.com$/', $credentials['email']) && $anon_user_rec && !$user_rec) {
            $anon_user_rec->email = $credentials['email'];
            $anon_user_rec->save();
            //manual login
            Auth::login($anon_user_rec);

            return $this->response->withItem($this->auth->user(), new UserTransformer())->header('X-Auth-Token', $this->getAuthToken());
        }

        return $this->response->errorUnauthorized('Unauthorized to login.');
    }

    /**
     * @param Request $request
     * @param Twilio $twilio
     * @return mixed
     */
    public function postRegister(Request $request)
    {
        $data = $request->all();

        $validator = $this->registrar->validator($data);

        if ($validator->fails()) {
            return $this->response->errorWrongArgs($validator->errors()->getMessages());
        }

        try {
            Stripe::setApiKey(\Config::get('services.stripe.secret'));
            $customer = StripeCustomer::create([
                "description" => $data["name"],
                "email" => $data['email'],
            ]);

        } catch (Exception $e) {}

        if(isset($data['stripe_token'])) {

            try {
                $customer->sources->create([
                    "source" => $data['stripe_token']
                ]);

                $data['stripe_customer_id'] = $customer->id;

            } catch (Exception $e) {}
        }

        try {

            $data['referral_code'] = User::generateReferralCode();

            $this->auth->login($this->registrar->create($data));

            $this->auth->user()->attachRole(3);

            if( ! empty($data['email']) && ! preg_match('/squeegyapp-tmp.com$/', $data['email'])) {
                \Event::fire(new UserRegistered());
            }

        } catch(Exception $e) {
            return $this->response->errorInternalError($e->getMessage());
        }

        return $this->response->withItem($this->auth->user(), new UserTransformer())->header('X-Auth-Token', $this->getAuthToken());
    }

    /**
     * @return mixed
     */
    public function getLogout()
    {
        $this->auth->logout();

        return $this->response->withArray([
            'message' => 'Success',
            'status_code' => 200
        ]);
    }

    private function getAuthToken()
    {
        $token="";
        try {
            $token = JWTAuth::fromUser($this->auth->user());
        } catch (JWTException $e) {
            \Bugsnag::notifyException($e);
        }
        return $token;
    }

}
