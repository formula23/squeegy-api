<?php namespace App\Providers;

use Illuminate\Session\SessionServiceProvider;

class ApiSessionServiceProvider extends SessionServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->registerSessionManager();

        $this->registerSessionDriver();

        $this->app->singleton('App\Http\Middleware\ApiSession');
	}

}
