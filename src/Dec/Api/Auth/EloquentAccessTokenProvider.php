<?php namespace Dec\Api\Auth;

use Carbon\Carbon;
use Dec\Api\Models\AccessToken;
use Exception;
use Illuminate\Auth\UserInterface;
use Illuminate\Database\Connection;
use Illuminate\Encryption\Encrypter;

class EloquentAccessTokenProvider implements AccessTokenProviderInterface {

    /**
     * Encrypter instance
     *
     * @var \Illuminate\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * Hasher instance
     *
     * @var \Dec\Api\Auth\HashProvider
     */
    protected $hasher;

    /**
     * Default token expiry
     * @var Carbon
     */
    protected $defaultExpiry;

    /**
     * @param Encrypter $encrypter
     * @param HashProvider $hasher
     */
    function __construct(Encrypter $encrypter, HashProvider $hasher)
    {
        $this->encrypter = $encrypter;
        $this->hasher = $hasher;
        $this->defaultExpiry = Carbon::now()->addWeeks(4);
    }

    /**
     * Creates an auth token for user.
     *
     * @param \Illuminate\Auth\UserInterface $user
     * @return \Dec\Api\Models\AccessToken|false
     */
    public function create(UserInterface $user, $expires = true)
    {
        if ($user == null || $user->getAuthIdentifier() == null)
            return false;

        $accessToken = $this->generateAccessToken($user->getAuthIdentifier(), $expires);

        if (!$accessToken->save())
            return false;

        return $accessToken;
    }

    protected function generateAccessToken($userId, $expires = true, $publicKey = null)
    {
        if (empty($publicKey))
            $publicKey = $this->hasher->make();

        $privateKey = $this->hasher->makePrivate($publicKey);

        if ($expires === false)
        {
            $expires = null;
        }
        else
        {
            // Try and parse it, true and null will throw exceptions
            try
            {
                $expires = new Carbon($expires);
            }
            catch(Exception $e)
            {
                // Can't parse it, so set to default
                $expires = $this->defaultExpiry;
            }
        }


        $accessToken                = new AccessToken;
        $accessToken->user_id       = $userId;
        $accessToken->public_key    = $publicKey;
        $accessToken->private_key   = $privateKey;
        $accessToken->expires       = $expires;

        return $accessToken;
    }

    /**
     * Find user id from auth token.
     *
     * @param $serializedAccessToken string
     * @return \Dec\Api\Models\AccessToken|null
     */
    public function find($serializedAccessToken)
    {
        // Get userId and public key
        $accessToken = $this->deserializeToken($serializedAccessToken);

        if($accessToken == null)
            return null;

        if(!$this->verifyKeys($accessToken->public_key, $accessToken->private_key))
            return null;

        return $accessToken;
    }

    protected function verifyKeys($publicKey, $privateKey)
    {
        return $this->hasher->check($publicKey, $privateKey);
    }

    /**
     * Returns serialized token.
     *
     * @param AccessToken $token
     * @return string
     */
    public function serializeToken(AccessToken $token)
    {
        $payload = [
            'user_id' => $token->user_id,
            'public_key' => $token->public_key
        ];

        return $this->encrypter->encrypt($payload);
    }

    /**
     * Deserializes token.
     *
     * @param string $payload
     * @return AccessToken|null
     */
    public function deserializeToken($payload)
    {
        try
        {
            $data = $this->encrypter->decrypt($payload);
        }
        catch (DecryptException $e)
        {
            return null;
        }

        if (empty($data['user_id']) || empty($data['public_key']))
            return null;

        $privateKey = $this->hasher->makePrivate($data['public_key']);

        $accessToken = AccessToken::where(function($query) use ($data, $privateKey) {
            $query->where('user_id',        $data['user_id'])
                  ->where('public_key',     $data['public_key'])
                  ->where('private_key',    $privateKey);
        })->first();

        return $accessToken;
    }

    /**
     * @param mixed|\Illuminate\Auth\UserInterface $identifier
     * @return bool
     */
    public function purge($identifier)
    {
        if ($identifier instanceof UserInterface)
            $identifier = $identifier->getAuthIdentifier();

        $result = AccessToken::where('user_id', $identifier)->delete();

        return $result > 0;
    }

    public function delete($accessToken)
    {
        if (is_string($accessToken))
            $accessToken = $this->deserializeToken($accessToken);

        if (!is_a($accessToken, '\Dec\Api\Models\AccessToken'))
            return false;

        return $accessToken->delete();
    }

}