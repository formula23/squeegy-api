<?php

namespace App\Http\Middleware;

use Closure;
use App;
use Illuminate\Support\Facades\Config;

class LogResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $apiLog = App::make(Config::get('apiguard.apiLogModel', 'Chrisbjr\ApiGuard\Models\ApiLog'));
        $apiLog = $apiLog->find($request->api_log_id);
        
        $apiLog->status_code = $response->status();
        $apiLog->response_body = $response->getContent();
        $apiLog->save();

        return $response;
    }
}