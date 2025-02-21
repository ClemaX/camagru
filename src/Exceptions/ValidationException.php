<?php

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected array $errors;

    public function __construct(array $errors, string $message = "Validation failed", int $code = 400)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public static function withMessages(array $messages): self
    {
        return new static($messages);
    }
}
