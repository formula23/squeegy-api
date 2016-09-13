<?php namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {

	/**
	 * The application's global HTTP middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = [
		'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
		'Illuminate\Cookie\Middleware\EncryptCookies',
		'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
//		'App\Http\Middleware\ApiSession',
		'Illuminate\View\Middleware\ShareErrorsFromSession',
		'App\Http\Middleware\AfterMiddleware',
		\App\Http\Middleware\Cors::class,
	];

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
		'auth' => 'App\Http\Middleware\Authenticate',
		'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
        'auth.api' => 'App\Http\Middleware\ApiAuthenticate',
		'guest' => 'App\Http\Middleware\RedirectIfAuthenticated',
        'csrf' => 'App\Http\Middleware\VerifyCsrfToken',
        'is.worker' => 'App\Http\Middleware\IsWorker',
		'jwt.auth' => 'Tymon\JWTAuth\Middleware\GetUserFromToken',
		'jwt.refresh' => 'Tymon\JWTAuth\Middleware\RefreshToken',
		'user_has_access' => 'App\Http\Middleware\RequestingUserHasAccessMiddleware',
	];

}
