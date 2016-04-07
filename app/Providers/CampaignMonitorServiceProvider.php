<?php

namespace App\Providers;

use App\Squeegy\CampaignMonitor\CampaignMonitor;
use Illuminate\Support\ServiceProvider;

class CampaignMonitorServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(){}

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/campaignmonitor.php', 'campaignmonitor');
        $this->app['campaignmonitor'] = $this->app->share(function ($app) {
            return new CampaignMonitor($app);
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return ['campaignmonitor'];
    }
}
