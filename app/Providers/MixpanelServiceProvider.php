<?php

namespace App\Providers;

use App\Observers\MixPanelUserObserver;
use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class MixpanelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Request $request
     * @param LaravelMixpanel $mixPanel
     */
    public function boot(Request $request, LaravelMixpanel $mixPanel)
    {
        $this->app->make(config('auth.model'))->observe(new MixPanelUserObserver($request, $mixPanel));
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
