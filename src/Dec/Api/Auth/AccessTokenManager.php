<?php namespace Dec\Api\Auth;

use Illuminate\Support\Manager;

class AccessTokenManager extends Manager {

    protected function createEloquentDriver()
    {
        $provider = $this->createEloquentProvider();
        $users = $this->app['auth']->driver()->getProvider();

        return new AccessTokenDriver($provider, $users);
    }

    protected function createEloquentProvider()
    {
        $encrypter = $this->app['encrypter'];
        $hasher = new HashProvider($this->app['config']['app.key']);

        return new EloquentAccessTokenProvider($encrypter, $hasher);
    }

    protected function getDefaultDriver()
    {
        return 'eloquent';
    }

}