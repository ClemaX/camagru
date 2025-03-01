<?php

class AuthException extends Exception
{
    public function __construct(string $message, int $code = 4010)
    {
        parent::__construct($message, $code);
    }
}
