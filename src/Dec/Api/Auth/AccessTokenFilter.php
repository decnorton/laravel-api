<?php namespace Dec\Api\Auth;

use Illuminate\Events\Dispatcher;
use Dec\Api\Exceptions\NotAuthorizedException;

class AccessTokenFilter {

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Events\Dispatcher
     */
    protected $events;

    /**
     * @var \Dec\Api\Auth\AccessTokenDriver
     */
    protected $driver;

    public function __construct(AccessTokenDriver $driver, Dispatcher $events)
    {
        $this->driver = $driver;
        $this->events = $events;
    }

    public function filter($route, $request)
    {
        $payload = $request->header('X-Access-Token');

        if (empty($payload))
            $payload = $request->input('access_token');

        $user = $this->driver->validate($payload);

        if (!$user)
            throw new NotAuthorizedException();

        $this->events->fire('access.token.valid', $user);
    }
}