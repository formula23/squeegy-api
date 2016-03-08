<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class PasswordController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Password Reset Controller
	|--------------------------------------------------------------------------
	|
	| This controller is responsible for handling password reset requests
	| and uses a simple trait to include this behavior. You're free to
	| explore this trait and override any methods you wish to tweak.
	|
	*/

	use ResetsPasswords;

	/**
	 * Create a new password controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\PasswordBroker  $passwords
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

//		$this->auth = $auth;
//		$this->passwords = $passwords;

	}

	/**
	 * Send a reset link to the given user.
	 *
	 * @param  Request $request
	 * @param TokenRepositoryInterface $tokens
	 * @return Response
	 */
	public function postEmail(Request $request, TokenRepositoryInterface $tokens)
	{

		$this->validate($request, ['email' => 'required|email']);

		$credentials = $request->only('email');
		$user = $this->passwords->getUser($credentials);

		if($user) {
			$token = $tokens->create($user);

			$this->passwords->emailResetLink($user, $token, function($m) use ($token) {

				$headers = $m->getHeaders();
				$mergevars=['RESET_PW_URL'=>env('WEBSITE_URL').'/password/reset/'.$token];
				$headers->addTextHeader('X-MC-MergeVars', json_encode($mergevars));
				$headers->addTextHeader('X-MC-Template', 'password-reset');

				$m->subject($this->getEmailSubject());
			});
		}

		return $this->response->withArray(['status'=>trans(PasswordBroker::RESET_LINK_SENT)]);

	}

	/**
	 * Reset the given user's password.
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function postReset(Request $request)
	{
		$this->validate($request, [
			'token' => 'required',
			'email' => 'required|email',
			'password' => 'required|confirmed',
		]);

		$credentials = $request->only(
			'email', 'password', 'password_confirmation', 'token'
		);

		$response = $this->passwords->reset($credentials, function($user, $password)
		{
			$user->anon_pw_reset = 1;
			$user->password = $password;
			$user->save();
		});

		switch ($response)
		{
			case PasswordBroker::PASSWORD_RESET:
				return $this->response->withArray(['status'=>trans($response)]);

			default:
				return $this->response->errorWrongArgs(trans($response));
		}
	}

}
