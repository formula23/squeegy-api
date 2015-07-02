<?php namespace App\Http\Controllers\Auth;

use App\Squeegy\Payments;
use Exception;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Http\Request;

use Aloha\Twilio\Twilio;
use Stripe\Stripe;
use Stripe\Customer as StripeCustomer;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Squeegy\Transformers\UserTransformer;

use Bugsnag;


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

        $this->middleware('auth.api');

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

        if ($this->auth->attempt($credentials, $request->has('remember')))
        {
            return $this->response->withItem($this->auth->user(), new UserTransformer());
        }

        return $this->response->errorUnauthorized('Unauthorized.');
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

        if ($validator->fails())
        {
            return $this->response->errorWrongArgs($validator->errors()->getMessages());
        }

        try {

            Stripe::setApiKey(\Config::get('stripe.api_key'));
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

            $this->auth->login($this->registrar->create($data));

            $this->auth->user()->attachRole(3);

            \Event::fire(new UserRegistered());

        } catch(Exception $e) {
            return $this->response->errorInternalError($e->getMessage());
        }

        return $this->response->withItem($this->auth->user(), new UserTransformer());
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
}
