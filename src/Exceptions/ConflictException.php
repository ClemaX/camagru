<?php

namespace App\Exceptions;

use Exception;

class ConflictException extends Exception
{
    protected string $field;

    public function __construct(string $field, string $message = "Conflict", int $code = 409)
    {
        parent::__construct($message, $code);
        $this->field = $field;
    }

    public function getField(): string
    {
        return $this->field;
    }
}
