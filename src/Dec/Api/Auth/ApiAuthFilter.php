<?php namespace Dec\Api\Auth;

use Illuminate\Events\Dispatcher;
use Dec\Api\Api;
use Dec\Api\Exceptions\NotAuthorizedException;

class ApiAuthFilter {

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Events\Dispatcher
     */
    protected $events;

    /**
     * @var \Dec\Api\Auth\ApiAuthDriver
     */
    protected $driver;

    public function __construct(ApiAuthDriver $driver, Dispatcher $events)
    {
        $this->driver = $driver;
        $this->events = $events;
    }

    public function filter($route, $request)
    {
        $token = $this->driver->token(Api::retrieveAccessToken());

        if ($token && $user = $token->user)
        {
            // Update last used timestamp
            $token->touchLastUsed();
            $token->save();

            $this->events->fire('api.auth.valid', $user);
        }

    }
}