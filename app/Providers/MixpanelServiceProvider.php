<?php

namespace App\Providers;

use App\Observers\UserObserver;
use GeneaLabs\LaravelMixpanel\LaravelMixpanel;
use GeneaLabs\LaravelMixpanel\Listeners\LaravelMixpanelUserObserver;
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
        Log::info('BOOT MixpanelServiceProvider');
        Log::info(config('auth.model'));

//        $this->app->make(config('auth.model'))->observe(new LaravelMixpanelUserObserver($request, $mixPanel));

        $this->app->make(config('auth.model'))->observe(new UserObserver($request, $mixPanel));

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
