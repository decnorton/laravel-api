<?php namespace Dec\Api\Auth;

use Illuminate\Support\Manager;

class ApiAuthManager extends Manager {

    public function getDefaultDriver()
    {
        return 'eloquent';
    }

    public function createEloquentDriver()
    {
        $provider = $this->createEloquentProvider();
        $users = $this->app['auth']->driver()->getProvider();

        return new ApiAuthDriver($provider, $users);
    }

    public function createEloquentProvider()
    {
        $encrypter = $this->app['encrypter'];
        $hasher = new HashProvider($this->app['config']['app.key']);

        return new EloquentApiAuthProvider($encrypter, $hasher);
    }

}
