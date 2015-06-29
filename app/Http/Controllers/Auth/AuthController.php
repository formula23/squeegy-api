<?php namespace App\Http\Controllers\Auth;

use Aloha\Twilio\Twilio;
use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Squeegy\Transformers\UserTransformer;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use League\Fractal\Manager;
use EllipseSynergie\ApiResponse\Laravel\Response as EllipseResponse;
use Stripe\Stripe;
use Stripe\Charge as Stripe_Charge;
use Stripe\Customer as Stripe_Customer;
use Chrisbjr\ApiGuard\Http\Controllers\ApiGuardController;
use Exception;


/**
 * Class AuthController
 * @package App\Http\Controllers\Auth
 */
class AuthController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Registration & Login Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles the registration of new users, as well as the
	| authentication of existing users.
	|
	*/


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
            $customer = Stripe_Customer::create([
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
