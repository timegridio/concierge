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

        // use this if your package needs a config file
        // $this->publishes([
        //         __DIR__.'/config/config.php' => config_path('concierge.php'),
        // ]);

        // use the vendor configuration file as fallback
        // $this->mergeConfigFrom(
        //     __DIR__.'/config/config.php', 'concierge'
        // );
    }
    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     *
     * @return void
     */
    public function setupRoutes(Router $router)
    {
        $router->group(['namespace' => 'Timegridio\Concierge\Http\Controllers'], function ($router) {
            require __DIR__.'/Http/routes.php';
        });
    }
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConcierge();

        // use this if your package has a config file
        // config([
        //         'config/concierge.php',
        // ]);
    }
    private function registerConcierge()
    {
        $this->app->bind('concierge', function ($app) {
            return new Concierge($app);
        });
    }
}
