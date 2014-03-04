<?php namespace Dec\Api\Auth;

use Dec\Api\Models\ApiSession;
use Illuminate\Auth\UserInterface;

interface ApiSessionProviderInterface {

    /**
     * Creates an API session for user.
     */
    public function create(UserInterface $user);

    /**
     * Find user id from session.
     *
     * @param $serializedSession string
     * @return \Dec\Api\Auth\ApiSession|null
     */
    public function find($serializedSession);

    /**
     * Returns serialized session.
     *
     * @param ApiSession $session
     * @return string
     */
    public function serializeSession(ApiSession $session);

    /**
     * Deserializes session.
     *
     * @param string $payload
     * @return ApiSession
     */
    public function deserializeSession($payload);

    /**
     * @param mixed|\Illuminate\Auth\UserInterface $identifier
     * @return bool
     */
    public function purge($identifier);
}