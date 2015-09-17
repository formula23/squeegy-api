<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class Authenticate {

	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard  $auth
	 * @return void
	 */
	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{

		if ($this->auth->guest())	
		{
    		\Bugsnag::notifyException(new \Exception("Unauthorized. Please login... Path:".$request->path()." -- ".print_r($request->all(), 1)));
            return response()->json(['error'=>'Unauthorized. Please login...', 'status_code'=>401], 401);
		}

		return $next($request);
	}

}
