<?php namespace Dec\Api\Auth;

use Illuminate\Events\Dispatcher;
use Dec\Api\Exceptions\NotAuthorizedException;

class ApiSessionFilter {

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Events\Dispatcher
     */
    protected $events;

    /**
     * @var \Dec\Api\Auth\ApiSessionDriver
     */
    protected $driver;

    public function __construct(ApiSessionDriver $driver, Dispatcher $events)
    {
        $this->driver = $driver;
        $this->events = $events;
    }

    public function filter($route, $request)
    {
        $token = Api::retrieveAccessToken();

        $user = $this->driver->validate($token);

        if (!$user)
            throw new NotAuthorizedException;

        $this->events->fire('api.session.valid', $user);
    }
}