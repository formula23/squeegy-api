<?php namespace App\Http\Middleware;

use App;
use Config;
use Input;
use Closure;
use Log;
use Exception;
use Chrisbjr\ApiGuard\Repositories\ApiKeyRepository;

class ApiAuthenticate {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
/*
        $key = $request->header(Config::get('apiguard.keyName', 'X-Authorization'));

        if (empty($key)) {
            // Try getting the key from elsewhere
            $key = Input::get(Config::get('apiguard.keyName', 'X-Authorization'));
        }

        if (empty($key)) {
            // It's still empty!
            return response()->json(['error'=>'Unauthorized. Please provide a valid API Key.', 'status_code'=>401], 401);
        }

        $apiKeyModel = App::make(Config::get('apiguard.model', 'Chrisbjr\ApiGuard\Models\ApiKey'));

        if ( ! $apiKeyModel instanceof ApiKeyRepository) {
            Log::error('[Chrisbjr/ApiGuard] You ApiKey model should be an instance of ApiKeyRepository.');
            $exception = new Exception("You ApiKey model should be an instance of ApiKeyRepository.");
            throw($exception);
        }

        $this->apiKey = $apiKeyModel->getByKey($key);

        if (empty($this->apiKey)) {
            return response()->json(['error'=>'Unauthorized. Please provide a valid API Key.', 'status_code'=>401], 401);
        }
*/

		return $next($request);
	}

}
