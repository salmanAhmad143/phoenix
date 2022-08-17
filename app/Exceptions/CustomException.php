<?php


namespace App\Exceptions;

Use Exception;

class CustomException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code,  $previous);
    }
}
