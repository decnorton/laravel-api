<?php namespace Dec\Api\Exceptions;

class NotAuthorizedException extends \Exception {

    public function __construct($message = "Not authorized", $code = 403, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}