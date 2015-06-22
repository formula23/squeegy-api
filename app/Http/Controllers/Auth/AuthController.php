<?php namespace App\Http\Controllers\Auth;

use Aloha\Twilio\Twilio;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\OctaneLA\Transformers\UserTransformer;
use App\User;
use Aws\Ecs\Exception\EcsException;
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

    public $manager;
    public $response;

	/**
	 * Create a new authentication controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
	 * @return void
	 */
	public function __construct(Guard $auth, Registrar $registrar)
	{
		$this->auth = $auth;
		$this->registrar = $registrar;
        $this->response = new EllipseResponse(new Manager);

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

        return $this->response->errorUnauthorized('Unauthorized');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function postRegister(Request $request, Twilio $twilio)
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

        } catch (\Exception $e) {
            return $this->response->errorInternalError('Unable to create account');
        }

        if(isset($data['stripe_token'])) {

            try {
                $customer->sources->create([
                    "source" => $data['stripe_token']
                ]);

            } catch (InvalidRequest $e) {
                return $this->response->errorWrongArgs($e->getMessage());
            } catch (\ErrorException $e) {
                return $this->response->errorInternalError($e->getMessage());
            }
        }

        $data['phone'] = "+1".$data["phone"];
        $data['stripe_customer_id'] = $customer->id;

        try {

            $this->auth->login($this->registrar->create($data));

            $this->auth->user()->attachRole(3);

            //send email
            Mail::send('emails.welcome', ['key' => 'value'], function ($message) use ($data) {
                $message->to($data['email'], $data['name'])->subject('Welcome to Squeegy!');
            });

            //send SMS phone verification
            $twilio->message($data['phone'], "Squeegy verification code: " . \Config::get('squeegy.sms_verification'));

        } catch(\Exception $e) {
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

        return Response::json([
            'message' => 'Success',
            'status_code' => 200
        ], 200);
    }
}
