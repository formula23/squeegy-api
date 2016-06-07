<?php namespace App\Providers;

use App\UserNote;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RouteServiceProvider extends ServiceProvider {

	/**
	 * This namespace is applied to the controller routes in your routes file.
	 *
	 * In addition, it is set as the URL generator's root namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'App\Http\Controllers';

	/**
	 * Define your route model bindings, pattern filters, etc.
	 *
	 * @param  \Illuminate\Routing\Router  $router
	 * @return void
	 */
	public function boot(Router $router)
	{
		parent::boot($router);
        $router->bind('vehicles', function($id) {
            return \App\Vehicle::find($id);
        });

        $router->bind('orders', function($id) {
            return \App\Order::find($id);
//            if(Auth::user() && Auth::user()->is('customer')) $order->where('user_id', Auth::id());
//            return $order->get()->first();
        });

        $router->model('users', 'App\User');

        $router->bind('notes', function($id, $route) {
            if($note = $route->users->notes()->where('id', $id)->first()) {
                return $note;
            }
            throw new NotFoundHttpException;
        });

        $router->model('services', 'App\Service');
	}

	/**
	 * Define the routes for the application.
	 *
	 * @param  \Illuminate\Routing\Router  $router
	 * @return void
	 */
	public function map(Router $router)
	{
		$router->group(['namespace' => $this->namespace], function($router)
		{
			require app_path('Http/routes.php');
		});
	}

}
