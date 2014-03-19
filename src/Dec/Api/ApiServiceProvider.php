<?php namespace Dec\Api;

use Illuminate\Support\ServiceProvider;
use Dec\Api\Auth\ApiAuthManager;
use Dec\Api\Auth\ApiAuthFilter;
use Dec\Api\Auth\AuthController;

class ApiServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('dec/api');
        $this->app['router']->filter('api.auth', 'api.auth.filter');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('api', 'Dec\Api\Api');
        $this->app->bind('transformer', 'Dec\Api\Transform\Transformer');

        $this->app->bindShared('api.auth', function($app)
        {
            return new ApiAuthManager($app);
        });

        $this->app->bindShared('api.auth.filter', function ($app) {
            $driver = $app['api.auth']->driver();
            $events = $app['events'];

            return new ApiAuthFilter($driver, $events);
        });

        $this->app->bind('Dec\Api\Auth\AuthController', function($app)
        {
            $driver = $app['api.auth']->driver();

            return new AuthController($driver);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('api.auth', 'api.auth.filter');
    }

}
