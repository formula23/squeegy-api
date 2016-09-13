<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Session\Middleware\StartSession;


class ApiSession extends StartSession {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		\Config::set('session.driver', 'array');
//        if($request->segment(2)=='services' || $request->header('Authorization'))
//        {
//
//        }

        return parent::handle($request, $next);
	}

}
