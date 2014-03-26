<?php namespace Dec\Api;

use Illuminate\Support\ServiceProvider;
use Dec\Api\Auth\ApiAuthManager;
use Dec\Api\Auth\ApiAuthFilter;
use Dec\Api\Auth\AuthController;
use Dec\Api\Filters\PermissionFilter;
use Dec\Api\Filters\RoleFilter;

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
        $this->app['router']->filter('api.auth.filter', 'api.auth.filter');
        $this->app['router']->filter('api.permission', 'api.filter.permission');
        $this->app['router']->filter('api.role', 'api.filter.role');
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

        $this->app->bindShared('api.auth.filter', function ($app)
        {
            $driver = $app['api.auth']->driver();
            $events = $app['events'];

            return new ApiAuthFilter($driver, $events);
        });

        $this->app->bindShared('api.filter.permission', function($app)
        {
            return new PermissionFilter;
        });

        $this->app->bindShared('api.filter.role', function($app)
        {
            return new RoleFilter;
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
        return [
            'api.auth',
            'api.auth.filter',
            'api.filter.permission',
            'api.filter.role'
        ];
    }

}
