<?php

require_once __DIR__ . '/AuthException.php';

class AuthLockedException extends AuthException
{
    public function __construct(
        string $message = "Your account is locked. Please click on the verification link sent to you via email.",
        int $code = 4011
    ) {
        parent::__construct($message, $code);
    }
}
