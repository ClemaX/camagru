<?php

namespace App\Exceptions;

use Exception;

class InternalException extends Exception
{
    public function __construct(
        string $message = "Internal Server Error",
        int $code = 409
    ) {
        parent::__construct($message, $code);
    }
}
