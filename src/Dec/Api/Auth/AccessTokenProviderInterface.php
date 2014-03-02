<?php namespace Dec\Api\Auth;

use Dec\Api\Models\AccessToken;
use Illuminate\Auth\UserInterface;

interface AccessTokenProviderInterface {

    /**
     * Creates an auth token for user.
     */
    public function create(UserInterface $user);

    /**
     * Find user id from auth token.
     *
     * @param $serializedAccessToken string
     * @return \Dec\Api\Auth\AccessToken|null
     */
    public function find($serializedAccessToken);

    /**
     * Returns serialized token.
     *
     * @param AccessToken $token
     * @return string
     */
    public function serializeToken(AccessToken $token);

    /**
     * Deserializes token.
     *
     * @param string $payload
     * @return AccessToken
     */
    public function deserializeToken($payload);

    /**
     * @param mixed|\Illuminate\Auth\UserInterface $identifier
     * @return bool
     */
    public function purge($identifier);
}