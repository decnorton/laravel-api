<?php namespace Dec\Api\Auth;

use Dec\Api\Models\ApiClient;
use Dec\Api\Models\ApiSession;
use Dec\Api\Exceptions\InvalidApiClientException;
use Dec\Api\Exceptions\NotAuthorizedException;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserProviderInterface;

class ApiAuthDriver {

    /**
     * @var \Dec\Api\Auth\ApiAuthProviderInterface
     */
    protected $provider;

    /**
     * @var \Illuminate\Auth\UserProviderInterface
     */
    protected $users;


    public function __construct(ApiAuthProviderInterface $provider, UserProviderInterface $users)
    {
        $this->provider = $provider;
        $this->users = $users;
    }

    public function client($clientPayload)
    {
        return $this->provider->findClient($clientPayload);
    }

    public function token($accessTokenPayload)
    {
        return $this->provider->findSession($accessTokenPayload);
    }

    /**
     * Validates a public auth token. Returns User object on success, otherwise false.
     *
     * @param   $token
     * @return  bool|UserInterface
     */
    public function user($token)
    {
        return $this->provider->findUser($token);
    }

    /**
     * Attempt to create an ApiSession from user credentials.
     *
     * @param array             $credentials
     * @param bool|Carbon       $expires        When the token should expire
     * @return bool|ApiSession
     */
    public function attempt(array $credentials, $client, $expires = true)
    {
        if (!$client = $this->provider->findClient($client))
            throw new InvalidApiClientException;

        // Email takes precedence over username
        if (isset($credentials['email']) && isset($credentials['username']))
            unset($credentials['username']);

        $user = $this->users->retrieveByCredentials($credentials);

        if($user instanceof UserInterface && $this->users->validateCredentials($user, $credentials))
             return $this->create($user, $client, $expires);

        return false;
    }

    /**
     * Create auth token for user.
     *
     * @param UserInterface $user
     * @return ApiSession
     */
    public function create(UserInterface $user, ApiClient $client, $expires = true)
    {
        return $this->provider->createSession($user, $client, $expires);
    }

    /**
     * Returns the ApiSessionInterface provider.
     *
     * @return \Dec\Api\Auth\ApiSessionProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }

}