<?php namespace Dec\Api\Auth;

use Carbon\Carbon;
use Dec\Api\Models\ApiSession;
use Exception;
use Illuminate\Auth\UserInterface;
use Illuminate\Database\Connection;
use Illuminate\Encryption\Encrypter;

class EloquentApiSessionProvider implements ApiSessionProviderInterface {

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
    public function __construct(Encrypter $encrypter, HashProvider $hasher)
    {
        $this->encrypter = $encrypter;
        $this->hasher = $hasher;
        $this->defaultExpiry = Carbon::now()->addWeeks(4);
    }

    /**
     * Creates an auth token for user.
     *
     * @param \Illuminate\Auth\UserInterface $user
     * @return \Dec\Api\Models\ApiSession|false
     */
    public function create(UserInterface $user, $expires = true)
    {
        if ($user == null || $user->getAuthIdentifier() == null)
            return false;

        $accessToken = $this->generateApiSession($user->getAuthIdentifier(), $expires);

        if (!$accessToken->save())
            return false;

        return $accessToken;
    }

    protected function generateApiSession($userId, $expires = true)
    {
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

        $sesssion              = new ApiSession;
        $sesssion->user_id     = $userId;
        $sesssion->public_key  = $publicKey;
        $sesssion->private_key = $privateKey;
        $sesssion->expires     = $expires;

        return $sesssion;
    }

    /**
     * Find user id from auth token.
     *
     * @param $serializedApiSession string
     * @return \Dec\Api\Models\ApiSession|null
     */
    public function find($serializedApiSession)
    {
        // Get userId and public key
        $accessToken = $this->deserializeSession($serializedApiSession);

        if($accessToken == null)
            return null;

        if(!$this->checkKeys($accessToken->public_key, $accessToken->private_key))
            return null;

        return $accessToken;
    }

    protected function checkKeys($publicKey, $privateKey)
    {
        return $this->hasher->check($publicKey, $privateKey);
    }

    /**
     * Returns serialized token.
     *
     * @param ApiSession $token
     * @return string
     */
    public function serializeSession(ApiSession $token)
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
     * @return ApiSession|null
     */
    public function deserializeSession($payload)
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

        $accessToken = ApiSession::where(function($query) use ($data, $privateKey) {
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

        $result = ApiSession::where('user_id', $identifier)->delete();

        return $result > 0;
    }

    public function delete($accessToken)
    {
        if (is_string($accessToken))
            $accessToken = $this->deserializeSession($accessToken);

        if (!is_a($accessToken, '\Dec\Api\Models\ApiSession'))
            return false;

        return $accessToken->delete();
    }

}