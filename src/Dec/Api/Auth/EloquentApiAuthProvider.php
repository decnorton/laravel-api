<?php namespace Dec\Api\Auth;

use Carbon\Carbon;
use Dec\Api\Models\ApiSession;
use Dec\Api\Models\ApiClient;
use Exception;
use Illuminate\Auth\UserInterface;
use Illuminate\Database\Connection;
use Illuminate\Encryption\Encrypter;

class EloquentApiAuthProvider implements ApiAuthProviderInterface {

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
     * @return \Dec\Api\Models\ApiSession
     */
    public function createSession(UserInterface $user, ApiClient $client, $expires = true)
    {
        if ($user == null || $user->getAuthIdentifier() == null)
            return null;

        $accessToken = $this->generateSession(
            $user->getAuthIdentifier(),
            $client->id,
            $expires
        );

        if (!$accessToken->save())
            return null;

        return $accessToken;
    }

    protected function generateSession($userId, $clientId, $expires = true)
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

        $session              = new ApiSession;
        $session->client_id   = $clientId;
        $session->user_id     = $userId;
        $session->public_key  = $publicKey;
        $session->private_key = $privateKey;
        $session->expires     = $expires;

        return $session;
    }

    public function findSession($serializedApiSession)
    {
        // Get userId and public key
        $apiSession = $this->deserializeSession($serializedApiSession);

        if($apiSession == null)
            return null;

        if(!$this->checkKeys($apiSession->public_key, $apiSession->private_key))
            return null;

        return $apiSession;
    }

    public function findUser($session)
    {
        if (!($session instanceof ApiSession))
            $session = $this->findSession($session);

        if (!$session)
            return null;

        return $session->user;
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
            'client_id'  => $token->client_id,
            'user_id'    => $token->user_id,
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

        if (empty($data['client_id']) || empty($data['user_id']) || empty($data['public_key']))
            return null;

        $privateKey = $this->hasher->makePrivate($data['public_key']);

        $accessToken = ApiSession::where(function($query) use ($data, $privateKey) {
            $query->where('user_id',        $data['user_id'])
                  ->where('client_id',      $data['client_id'])
                  ->where('public_key',     $data['public_key'])
                  ->where('private_key',    $privateKey);
        })->first();

        return $accessToken;
    }

    /**
     * @param mixed|\Illuminate\Auth\UserInterface $identifier
     * @return bool
     */
    public function purgeSessions($identifier)
    {
        if ($identifier instanceof UserInterface)
            $identifier = $identifier->getAuthIdentifier();

        $result = ApiSession::where('user_id', $identifier)->delete();

        return $result > 0;
    }

    public function deleteSession($accessToken)
    {
        if (is_string($accessToken))
            $accessToken = $this->deserializeSession($accessToken);

        if (!is_a($accessToken, '\Dec\Api\Models\ApiSession'))
            return false;

        return $accessToken->delete();
    }

    /**
     * Validate client. Accept id or name
     *
     * @param  string|int   $client
     * @return ApiClient
     */
    public function findClient($clientPayload)
    {
        if (!$clientPayload)
            return null;

        $client = ApiClient::where(function($query) use($clientPayload)
        {
            $query->where('id', $clientPayload)
                  ->orWhere('name', $clientPayload);
        })->first();

        return $client;
    }

    /**
     * Validate client. Accept id or name
     *
     * @param  string|int   $client
     * @return boolean
     */
    public function validateClient($clientPayload)
    {
        return $this->findClient($clientPayload) != null;
    }
}