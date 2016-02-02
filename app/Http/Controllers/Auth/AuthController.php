<?php namespace App\Http\Controllers\Auth;

use App\Squeegy\Payments;
use App\User;
use Exception;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Http\Request;

use Aloha\Twilio\Twilio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        if ($this->auth->attempt($credentials, $request->has('remember')))
        {
            if($request->header('X-Device-Identifier')) {

                $this->auth->user()->device_id = $request->header('X-Device-Identifier');

                $users = User::where('email','like',$request->header('X-Device-Identifier').'%')->orderBy('created_at','desc');
                if($users->count()) {
                    $latest_user = $users->first();
                    $sns_col = ( $request->header('X-Device') == "Android" ? "target_arn_gcm" : "push_token" );
                    if($latest_user->{$sns_col}) {
                        $this->auth->user()->{$sns_col} = $latest_user->{$sns_col};
                    }
                    try {
                        $users->limit(100);
                        $users->delete();
                    } catch(\Exception $e) {
                        \Bugsnag::notifyException($e);
                    }
                }

                $this->auth->user()->save();
            }

            //successful log
            if($request->input('anon_email')) {
                try {
                    if(preg_match('/squeegyapp-tmp.com$/', $request->input('anon_email'))) {
                        User::where('email', $request->input('anon_email'))->delete();
                    }
                } catch(\Exception $e) {
                    \Bugsnag::notifyException($e);
                }
            }

            if($request->header('X-Application-Type'))
            {
                switch(strtolower($request->header('X-Application-Type'))) {
                    case "consumer":
                        if( ! $this->auth->user()->is('customer')) return $this->response->errorUnauthorized('Account not authorized for this application.');
                        break;
                    case "washer":
                        if( ! $this->auth->user()->is('worker')) return $this->response->errorUnauthorized('Account not authorized for this application.');
                        break;
                }
            }

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
        Log::info('AuthController@postRegister');

        $data = $request->all();

        Log::info($data);

        $validator = $this->registrar->validator($data);

        if ($validator->fails()) {
            foreach($validator->errors()->getMessages() as $msgs) {
                $msg = implode(",", (array)$msgs);
            }
            return $this->response->errorWrongArgs($msg);
        }

        try {
            Log::info('Create stripe customer');
            Stripe::setApiKey(\Config::get('services.stripe.secret'));
            $customer = StripeCustomer::create([
                "description" => $data["name"],
                "email" => $data['email'],
            ]);

            Log::info('Stripe customer id: '.$customer->id);

            $data['stripe_customer_id'] = $customer->id;

        } catch (Exception $e) {}

        if(isset($data['stripe_token'])) {

            try {
                $customer->sources->create([
                    "source" => $data['stripe_token']
                ]);
            } catch (Exception $e) {}
        }

        try {

            $data['referral_code'] = User::generateReferralCode();
            $data['device_id'] = $request->header('X-Device-Identifier');

            Log::info($data);
            Log::info('Create and login');
            $this->auth->login($this->registrar->create($data));

            Log::info($this->auth->user());

            $this->auth->user()->attachRole(3);

            $user = User::find($this->auth->user()->id);

            if( ! empty($data['email']) && ! preg_match('/squeegyapp-tmp.com$/', $data['email'])) {
                \Event::fire(new UserRegistered());
            }

        } catch(Exception $e) {
            return $this->response->errorInternalError($e->getMessage());
        }

        return $this->response->withItem($user, new UserTransformer())->header('X-Auth-Token', $this->getAuthToken());
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
