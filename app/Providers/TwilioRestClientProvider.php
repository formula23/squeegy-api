<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Services_Twilio;

class TwilioRestClientProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'Services_Twilio', function ($app) {
            $twilio_config = \Config::get('twilio.twilio.connections.twilio');
            return new Services_Twilio($twilio_config['sid'], $twilio_config['token']);
        });
    }
}
