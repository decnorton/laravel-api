<?php namespace Dec\Api\Auth;

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserProviderInterface;
use Dec\Api\Models\ApiSession;
use Dec\Api\Exceptions\NotAuthorizedException;

class ApiSessionDriver {
    /**
     * @var \Dec\Api\Auth\ApiSessionProviderInterface
     */
    protected $tokens;

    /**
     * @var \Illuminate\Auth\UserProviderInterface
     */
    protected $users;

    public function __construct(EloquentApiSessionProvider $tokens, UserProviderInterface $users)
    {
        $this->tokens = $tokens;
        $this->users = $users;
    }

    /**
     * Returns the ApiSessionInterface provider.
     *
     * @return \Dec\Api\Auth\ApiSessionProviderInterface
     */
    public function getProvider()
    {
        return $this->tokens;
    }

    /**
     * Validates a public auth token. Returns User object on success, otherwise false.
     *
     * @param   $accessTokenPayload
     * @return  bool|UserInterface
     */
    public function validate($accessTokenPayload)
    {
        if ($accessTokenPayload == null)
            return false;

        $tokenResponse = $this->tokens->find($accessTokenPayload);

        if ($tokenResponse == null)
            return false;

        $user = $this->users->retrieveByID($tokenResponse->user_id);

        if ($user == null)
            return false;

        return $user;
    }

    /**
     * Attempt to create an ApiSession from user credentials.
     *
     * @param array             $credentials
     * @param bool|Carbon       $expires        When the token should expire
     * @return bool|ApiSession
     */
    public function attempt(array $credentials, $expires = true)
    {
        // Email takes precedence over username
        if (isset($credentials['email']) && isset($credentials['username']))
            unset($credentials['username']);

        $user = $this->users->retrieveByCredentials($credentials);

        if($user instanceof UserInterface && $this->users->validateCredentials($user, $credentials))
             return $this->create($user, $expires);

        return false;
    }

    /**
     * Create auth token for user.
     *
     * @param UserInterface $user
     * @return bool|ApiSession
     */
    public function create(UserInterface $user, $expires = true)
    {
        return $this->tokens->create($user, $expires);
    }

    /**
     * Retrieve user from auth token.
     *
     * @param ApiSession $token
     * @return UserInterface|null
     */
    public function user(ApiSession $token)
    {
        return $this->users->retrieveByID($token->user_id);
    }

    /**
     * Serialize token for public use.
     *
     * @param ApiSession $token
     * @return string
     */
    public function publicToken(ApiSession $token)
    {
        return $this->tokens->serializeToken($token);
    }
}