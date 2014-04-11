<?php namespace Dec\Api\Exceptions;

class InvalidTokenException extends \Exception {

    public function __construct($message = "Invalid token", $code = 400, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}