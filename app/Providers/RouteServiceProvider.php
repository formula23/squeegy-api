<?php namespace App\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Response;

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

            return \App\Vehicle::where('user_id', \Auth::id())->find($id);
        });

        $router->bind('locations', function($id) {
            return \App\Location::where('user_id', \Auth::id())->find($id);
        });

        $router->bind('orders', function($id) {
            $order = \App\Order::query()->where('id', $id);
            if(\Auth::user()->is('customer')) $order->where('user_id', \Auth::id());
            return $order->get()->first();
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
