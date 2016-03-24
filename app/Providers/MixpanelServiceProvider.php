<?php

namespace App\Providers;

use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
use GeneaLabs\LaravelMixpanel\Listeners\LaravelMixpanelUserObserver;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class MixpanelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(Request $request, LaravelMixpanel $mixPanel)
    {
        $this->app->make(config('auth.model'))->observe(new LaravelMixpanelUserObserver($request, $mixPanel));
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function provides()
    {
        return ['laravel-mixpanel'];
    }
}
