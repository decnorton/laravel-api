<?php namespace Dec\Api\Auth;

use Illuminate\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;

abstract class AbstractAccessTokenProvider implements AccessTokenProviderInterface {

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
     * @param Encrypter $encrypter
     * @param HashProvider $hasher
     */
    function __construct(Encrypter $encrypter, HashProvider $hasher)
    {
        $this->encrypter = $encrypter;
        $this->hasher = $hasher;
    }

    protected  function generateAccessToken($publicKey = null)
    {
        if (empty($publicKey))
            $publicKey = $this->hasher->make();

        $privateKey = $this->hasher->makePrivate($publicKey);

        return new AccessToken(null, $publicKey, $privateKey);
    }

    protected function verifyAccessToken(AccessToken $token)
    {
        return $this->hasher->check($token->getPublicKey(), $token->getPrivateKey());
    }

    /**
     * Returns serialized token.
     *
     * @param AccessToken $token
     * @return string
     */
    public function serializeToken(AccessToken $token)
    {
        $payload = array('id' => $token->getAuthIdentifier(), 'key' => $token->getPublicKey());

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
        try {
            $data = $this->encrypter->decrypt($payload);
        } catch (DecryptException $e) {
            return null;
        }

        if (empty($data['id']) || empty($data['key']))
            return null;

        $token = $this->generateAccessToken($data['key']);
        $token->setAuthIdentifier($data['id']);

        return $token;
    }

    /**
     * Getter for $hasher
     *
     * @return \Dec\Api\Auth\HashProvider
     */
    public function getHasher()
    {
        return $this->hasher;
    }
}