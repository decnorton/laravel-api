<?php namespace Dec\Api;

use Dec\Api\Util\AccessToken;
use Input;
use Request;
use Rhumsaa\Uuid\Uuid;

class Api {

    public function __construct()
    {

    }

    public static function retrieveAccessToken()
    {
        $token = Request::header('X-Access-Token');

        if (empty($token))
            $token = Input::get('access_token');

        return $token;
    }
}