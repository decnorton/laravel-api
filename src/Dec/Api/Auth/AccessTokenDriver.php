<?php namespace Dec\Api\Auth;

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\UserProviderInterface;
use Dec\Api\Models\AccessToken;
use Dec\Api\Exceptions\NotAuthorizedException;

class AccessTokenDriver {
    /**
     * @var \Dec\Api\Auth\AccessTokenProviderInterface
     */
    protected $tokens;

    /**
     * @var \Illuminate\Auth\UserProviderInterface
     */
    protected $users;

    public function __construct(AccessTokenProviderInterface $tokens, UserProviderInterface $users)
    {
        $this->tokens = $tokens;
        $this->users = $users;
    }

    /**
     * Returns the AccessTokenInterface provider.
     *
     * @return \Dec\Api\Auth\AccessTokenProviderInterface
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
     * Attempt to create an AccessToken from user credentials.
     *
     * @param array             $credentials
     * @param bool|Carbon       $expires        When the token should expire
     * @return bool|AccessToken
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
     * @return bool|AccessToken
     */
    public function create(UserInterface $user, $expires = true)
    {
        return $this->tokens->create($user, $expires);
    }

    /**
     * Retrieve user from auth token.
     *
     * @param AccessToken $token
     * @return UserInterface|null
     */
    public function user(AccessToken $token)
    {
        return $this->users->retrieveByID($token->user_id);
    }

    /**
     * Serialize token for public use.
     *
     * @param AccessToken $token
     * @return string
     */
    public function publicToken(AccessToken $token)
    {
        return $this->tokens->serializeToken($token);
    }
}