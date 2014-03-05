<?php namespace Dec\Api\Auth;

use Dec\Api\Models\ApiSession;
use Illuminate\Auth\UserInterface;

interface ApiAuthProviderInterface {

    /**
     * Creates an API session for user.
     */
    public function createSession(UserInterface $user);

    /**
     * Find user id from session.
     *
     * @param $serializedSession string
     * @return \Dec\Api\Auth\ApiSession|null
     */
    public function findSession($serializedSession);

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
     * Purge all sessions for user
     *
     * @param mixed|\Illuminate\Auth\UserInterface $user
     * @return bool
     */
    public function purgeSessions($user);


    /**
     * Validate client. Accept id or name
     *
     * @param  string|int   $client
     * @return ApiClient
     */
    public function findClient($client);

    /**
     * Validate client. Accept id or name
     *
     * @param  string|int   $client
     * @return boolean
     */
    public function validateClient($client);
}