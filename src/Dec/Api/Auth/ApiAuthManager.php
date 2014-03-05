<?php namespace Dec\Api\Auth;

use Illuminate\Support\Manager;

class ApiAuthManager extends Manager {

    protected function getDefaultDriver()
    {
        return 'eloquent';
    }

    protected function createEloquentDriver()
    {
        $provider = $this->createEloquentProvider();
        $users = $this->app['auth']->driver()->getProvider();

        return new ApiSessionDriver($provider, $users);
    }

    protected function createEloquentProvider()
    {
        $encrypter = $this->app['encrypter'];
        $hasher = new HashProvider($this->app['config']['app.key']);

        return new EloquentApiAuthProvider($encrypter, $hasher);
    }

}