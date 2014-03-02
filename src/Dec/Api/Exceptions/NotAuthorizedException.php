<?php namespace Dec\Api\Exceptions;

class NotAuthorizedException extends \Exception {

    public function __construct($message = "Not Authorized", $code = 401, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}