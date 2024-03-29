<?php namespace App\Http\Controllers\Auth;

use App\Events\UserCreated;
use App\Squeegy\Payments;
use App\User;
use Carbon\Carbon;
use Exception;

use Facebook\Exceptions\FacebookSDKException;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;

use Aloha\Twilio\Twilio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

use SammyK\LaravelFacebookSdk\LaravelFacebookSdk;
use Illuminate\Support\Facades\Validator;

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

    use AuthenticatesAndRegistersUsers;

    protected $apiMethods = [
        'postLogin' => [
            'logged' => true,
        ],
        'postRegister' => [
            'logged' => true,
        ]
    ];

    protected $user;

	/**
	 * Create a new authentication controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
        parent::__construct();
	}

    /**
     * @param Request $request
     * @param LaravelFacebookSdk $fb
     * @return mixed
     */
    public function postLogin(Request $request, LaravelFacebookSdk $fb)
    {
        $std_user=null;
        $facebook_user=null;
        if($request->input('facebook_id') && $request->input('facebook_token')) { //facebook login

            //verify FB token passed is valid token for user and fb app.
            try {
                $response = $fb->get('/me?fields=id,name,email,gender,birthday,age_range&access_token='.$request->input('facebook_token'));
                $fb_user = $response->getGraphUser();
                if($fb_user->getId() != $request->input('facebook_id')) {
                    return $this->response->errorWrongArgs('Unable to login with Facebook');
                }
            } catch(FacebookSDKException $e) {
                return $this->response->errorWrongArgs('Unable to login with Facebook');
            }

            $facebook_user_qry = User::where('facebook_id', $request->input('facebook_id'))->where('tmp_fb', 0)->orderBy('created_at', 'desc');
            if($request->input('email') && $request->input('email') == $fb_user->getEmail()) $facebook_user_qry->orWhere('email', $request->input('email'));

            $facebook_user = $facebook_user_qry->first();

            if( ! $facebook_user) {
                $anon_user = User::where('email','like',$request->header('X-Device-Identifier').'%')->orderBy('created_at','desc')->first();
                if($anon_user) {
                    $anon_user->tmp_fb = 1;
                    $anon_user->email = ($fb_user->getEmail()?:$request->header('X-Device-Identifier')."-".random_int(100, 9999999)."@squeegyapp-tmp.com");
                    $anon_user->updateFbFields($fb_user);

                    Event::fire(new UserRegistered($anon_user));
                }
                return $this->response->errorWrongArgs('You do not have an account. Please register.');
                
            } else if( ! $facebook_user->is_active ) {
                return $this->response->errorWrongArgs('Unauthorized to login.');
                
            }
            
            $facebook_user->updateFbFields($fb_user);

            Auth::login($facebook_user);

        } else {
            $data_to_validate=[
                'email' => 'required|email',
                'password' => 'required',
            ];
            $this->validate($request, $data_to_validate);

            $credentials = $request->only('email', 'password');
            $credentials['is_active'] = 1;

            $std_user = Auth::attempt($credentials, $request->has('remember'));
        }

        if ($std_user || $facebook_user)
        {
            if($request->header('X-Device-Identifier')) {

                $this->user()->device_id = $request->header('X-Device-Identifier');

                $users = User::where('email','like',$request->header('X-Device-Identifier').'%')->orderBy('created_at','desc');
                if($users->count()) {
                    $latest_user = $users->first();
                    $sns_col = ( $request->header('X-Device') == "Android" ? "target_arn_gcm" : "push_token" );
                    if($latest_user->{$sns_col}) {
                        $this->user()->{$sns_col} = $latest_user->{$sns_col};
                    }
                    try {
                        Log::info("******************* AUTH CONTROLLER ************************************************************");
                        User::where('email','like',$request->header('X-Device-Identifier').'%')->orderBy('created_at','desc')->get()->each(function($row){
                            $row->delete();
                        });
//
//                        User::where('id','=',$id)->get()
//                        $users_to_delete->delete();
                    } catch(\Exception $e) {
                        Log::error($e);
                        \Bugsnag::notifyException($e);
                    }
                }

                $this->user()->save();
            }

            //successful log
//            if($request->input('anon_email')) {
//                try {
//                    if(preg_match('/squeegyapp-tmp.com$/', $request->input('anon_email'))) {
//                        User::where('email', $request->input('anon_email'))->delete();
//                    }
//                } catch(\Exception $e) {
//                    \Bugsnag::notifyException($e);
//                }
//            }

            if($request->header('X-Application-Type'))
            {
                switch(strtolower($request->header('X-Application-Type'))) {
                    case "consumer":
                        if( ! $this->user()->is('customer')) return $this->response->errorUnauthorized('Account not authorized for this application.');
                        break;
                    case "washer":
                        if( ! $this->user()->is('worker')) return $this->response->errorUnauthorized('Account not authorized for this application.');
                        break;
                }
            }

            return $this->response->withItem($this->user(), new UserTransformer())->header('X-Auth-Token', $this->getAuthToken());
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

            return $this->response->withItem($this->user(), new UserTransformer())->header('X-Auth-Token', $this->getAuthToken());
        }

        return $this->response->errorUnauthorized('Unauthorized to login.');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function postRegister(Request $request)
    {
        $data = $request->all();

        $validator = $this->validator($data);

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

            Auth::login($this->create($data));

            $this->user()->attachRole(3);

            \Event::fire(new UserCreated($this->user()));

            if( ! empty($data['email']) && ! preg_match('/squeegyapp-tmp.com$/', $data['email'])) {
                $this->user()->anon_pw_reset = true;
                \Event::fire(new UserRegistered($this->user()));
            }

            $this->user()->push();

            $user = User::find($this->user()->id);

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
        Auth::user()->activity_logs()->whereNull('logout')->update([
            'logout' => Carbon::now(),
        ]);

        Auth::logout();

        return $this->response->withArray([
            'message' => 'Success',
            'status_code' => 200
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data)
    {
        return Validator::make($data, [
//			'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:8',
//            'phone' => 'required|digits:10',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    public function create(array $data)
    {

        return User::create([
            'name' => ! empty($data['name']) ? $data['name'] : '',
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => ! empty($data['phone']) ? $data['phone'] : '',
            'stripe_customer_id' => (isset($data['stripe_customer_id']) ? $data['stripe_customer_id']:null),
            'push_token' => ! empty($data['push_token']) ? $data['push_token'] : '',
            'referral_code' => ! empty($data['referral_code']) ? $data['referral_code'] : User::generateReferralCode(),
            'device_id' => ! empty($data['device_id']) ? $data['device_id'] : '',
        ]);
    }

    private function getAuthToken()
    {
        $token="";
        try {
            $token = JWTAuth::fromUser($this->user());
        } catch (JWTException $e) {
            \Bugsnag::notifyException($e);
        }
        return $token;
    }

    protected function user()
    {
        return Auth::user();
    }

}
