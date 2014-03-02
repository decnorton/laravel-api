<?php namespace Dec\Api\Auth;

class HashProvider {

    private $algo = 'sha256';
    private $hashKey;


    function __construct($hashKey)
    {
        $this->hashKey = $hashKey;
    }

    public function getAlgo()
    {
        return $this->algo;
    }

    public function getHashKey()
    {
        return $this->hashKey;
    }

    public function make($entropy = null)
    {
        if (!$entropy)
            $entropy = $this->generateEntropy();

        return hash($this->algo, $entropy);
    }

    public function makePrivate($publicKey)
    {
        return hash_hmac($this->algo, $publicKey, $this->hashKey);
    }

    public function check($publicKey, $privateKey)
    {
        return $privateKey == $this->makePrivate($publicKey);
    }

    public function generateEntropy()
    {
        return mcrypt_create_iv(32, $this->getRandomizer()) . uniqid(mt_rand(), true);
    }

    protected function getRandomizer()
    {
        if (defined('MCRYPT_DEV_URANDOM'))
            return MCRYPT_DEV_URANDOM;

        if (defined('MCRYPT_DEV_RANDOM'))
            return MCRYPT_DEV_RANDOM;

        mt_srand();

        return MCRYPT_RAND;
    }
}