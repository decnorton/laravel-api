<?php namespace Dec\Api\Exceptions;

class TokenExpiredException extends \Exception {

    public function __construct($message = "Expired token", $code = 401, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}