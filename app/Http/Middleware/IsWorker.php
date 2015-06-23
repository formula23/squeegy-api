<?php namespace App\Http\Middleware;

use Closure;

class IsWorker {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
        if( ! $request->user()->is('worker')) {
            return response()->json(['error'=>'Unauthorized.', 'status_code'=>401], 401);
        }
		return $next($request);
	}

}
