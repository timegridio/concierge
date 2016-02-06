<?php

namespace Timegridio\Concierge;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class TimegridioConciergeServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // use this if your package has views
        $this->loadViewsFrom(realpath(__DIR__.'/resources/views'), 'concierge');

        $this->publishes([
            __DIR__.'/../migrations/' => base_path('/database/migrations'),
        ]);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConcierge();
    }

    /**
     * Register package.
     *
     * @return Concierge
     */
    private function registerConcierge()
    {
        $this->app->bind('concierge', function ($app) {
            return new Concierge($app);
        });
    }
}
