<?php namespace App\Http\Controllers\Auth;

use App\Events\UserCreated;
use App\Squeegy\Payments;
use App\User;
use Exception;

use Facebook\Exceptions\FacebookSDKException;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Http\Request;

use Aloha\Twilio\Twilio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use SammyK\LaravelFacebookSdk\LaravelFacebookSdk;
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
	}

    /**
     * @param Request $request
     * @param LaravelFacebookSdk $fb
     * @return mixed
     */
    public function postLogin(Request $request, LaravelFacebookSdk $fb)
    {
        $facebook_user=null;
        $std_user=null;
        if($request->input('facebook_id') && $request->input('facebook_token')) { //facebook login

            //verify FB token passed is valid token for user and fb app.
            try {
                $response = $fb->get('/me?fields=id,name,email&access_token='.$request->input('facebook_token'));
                $fb_user = $response->getGraphUser();
                if($fb_user->getId() != $request->input('facebook_id')) {
                    return $this->response->errorWrongArgs('Unable to login with Facebook');
                }
            } catch(FacebookSDKException $e) {
                return $this->response->errorWrongArgs('Unable to login with Facebook');
            }

            $facebook_user = User::where('facebook_id', $request->input('facebook_id'))->orderBy('created_at', 'desc')->first();
            if( ! $facebook_user) {
                return $this->response->errorWrongArgs('You do not have an account. Please register.');
            }
            Auth::login($facebook_user);

            return $this->response->withItem($this->auth->user(), new UserTransformer())->header('X-Auth-Token', $this->getAuthToken());


        } else {
            $data_to_validate=[
                'email' => 'required|email',
                'password' => 'required',
            ];
            $this->validate($request, $data_to_validate);

            $credentials = $request->only('email', 'password');

            //little hack so that our accounts don't need to be active to log into the worker app
            //currently ETA calculations are done by the active state of an account.. so we can't be active.
            if( ! in_array($request->input('email'), ["dan@squeegy.com", "andrew@squeegy.com"])) {
                $credentials['is_active'] = 1;
            }

            $std_user = $this->auth->attempt($credentials, $request->has('remember'));
        }

        if ($std_user || $facebook_user)
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
                        $users_to_delete = User::where('email','like',$request->header('X-Device-Identifier').'%')->orderBy('created_at','desc');
                        $users_to_delete->delete();
                    } catch(\Exception $e) {
                        Log::error($e);
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

        Log::info($credentials['email'].' - Unable to login.');

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
            foreach($validator->errors()->getMessages() as $msgs) {
                $msg = implode(",", (array)$msgs);
            }
            return $this->response->errorWrongArgs($msg);
        }

        try {
            Stripe::setApiKey(\Config::get('services.stripe.secret'));
            $customer = StripeCustomer::create([
                "description" => (isset($data["name"])?:""),
                "email" => $data['email'],
            ]);

            $data['stripe_customer_id'] = $customer->id;

        } catch (Exception $e) {
            Log::error('Unable to create Stripe customer');
            \Bugsnag::notifyException($e);
        }

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

            $this->auth->login($this->registrar->create($data));

            $this->auth->user()->attachRole(3);

            \Event::fire(new UserCreated($this->auth->user()));

            if( ! empty($data['email']) && ! preg_match('/squeegyapp-tmp.com$/', $data['email'])) {
                $this->auth->user()->anon_pw_reset = true;
                \Event::fire(new UserRegistered($this->auth->user()));
            }

            $this->auth->user()->push();

            $user = User::find($this->auth->user()->id);

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
