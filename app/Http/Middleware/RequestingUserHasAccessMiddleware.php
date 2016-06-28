<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use EllipseSynergie\ApiResponse\Laravel\Response;
use League\Fractal\Manager;

class RequestingUserHasAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param $user_id
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $get_user_id = ($request->users?$request->users->id:$request->id);
        if( ! $request->user()->is('admin|support') && ($get_user_id != $request->user()->id)) {
            return (new Response(new Manager()))->errorWrongArgs();
        }
        return $next($request);
    }
}
