<?php

namespace App\Exceptions;

require_once __DIR__ . '/HttpException.php';

class InvalidCsrfTokenException extends HttpException
{
    public function __construct()
    {
        parent::__construct(
            401,
            "Invalid CSRF Token",
            "Your CSRF token is expired or invalid. Please go back, refresh and try again.",
            4012
        );
    }
}
