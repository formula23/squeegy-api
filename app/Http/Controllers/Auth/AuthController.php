<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\OctaneLA\Transformers\UserTransformer;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Response;
use League\Fractal\Manager;
use EllipseSynergie\ApiResponse\Laravel\Response as EllipseResponse;


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

		$this->middleware('guest', ['except' => ['getLogout']]);
        $this->middleware('auth', ['except' => ['postLogin', 'postRegister']]);
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
    public function postRegister(Request $request)
    {
        $validator = $this->registrar->validator($request->all());

        if ($validator->fails())
        {
            $this->throwValidationException(
                $request, $validator
            );
        }

        $this->auth->login($this->registrar->create($request->all()));

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
