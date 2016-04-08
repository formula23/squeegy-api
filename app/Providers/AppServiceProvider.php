<?php namespace App\Providers;

use Aloha\Twilio\Twilio;
use App\User;
use Aws\Laravel\AwsFacade;
use Aws\Sns\SnsClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}

	/**
	 * Register any application services.
	 *
	 * This service provider is a great spot to register your various container
	 * bindings with the application. As you can see, we are registering our
	 * "Registrar" implementation here. You can add your own bindings too!
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind(
			'Illuminate\Contracts\Auth\Registrar',
			'App\Services\Registrar'
		);

        if ($this->app->environment() == 'local') {
            $this->app->register('Laracasts\Generators\GeneratorsServiceProvider');
        }

        $this->app->bind('\Aloha\Twilio\Twilio', function() {
            $twilio_config = \Config::get('twilio.twilio.connections.twilio');
            return new Twilio($twilio_config['sid'], $twilio_config['token'], $twilio_config['from'], $twilio_config['ssl_verify']);
        });

        $this->app->bind('\Aws\Sns\SnsClient', function() {
			return AwsFacade::createClient('sns');
        });
	}

}
