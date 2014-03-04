<?php namespace Dec\Api;

use Illuminate\Support\ServiceProvider;
use Dec\Api\Auth\ApiSessionManager;
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


        $this->app->bindShared('api.session', function($app)
        {
            return new ApiSessionManager($app);
        });

        $this->app->bind('Dec\Api\Auth\AuthController', function($app)
        {
            $driver = $app['api.session']->driver();

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
        return array('api.session');
    }

}
