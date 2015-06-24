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
        if($request->segment(3)=='services')
        {
            \Config::set('session.driver', 'array');
        }

        return parent::handle($request, $next);
	}

}
