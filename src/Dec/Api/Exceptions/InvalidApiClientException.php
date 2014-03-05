<?php namespace Dec\Api\Exceptions;

class InvalidApiClientException extends \Exception {

    public function __construct($message = "Invalid API client", $code = 400, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}