<?php

namespace App\Exceptions;

use Exception;

class UnauthorizedException extends Exception
{
    public function __construct(
        string $message = "Unauthorized",
        int $code = 4010
    ) {
        parent::__construct($message, $code);
    }
}
