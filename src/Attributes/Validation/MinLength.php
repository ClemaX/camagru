<?php

namespace App\Attributes\Validation;

use Attribute;

require_once __DIR__ . '/ValidationInterface.php';

#[Attribute()]
class MinLength implements ValidationInterface
{
    public function __construct(private int $minLength)
    {
    }

    public function validate($value): ?string
    {
        if (strlen($value) < $this->minLength) {
            return "MIN_LENGTH_ERROR";
        }
        return null;
    }

    public function getConstraints(): array
    {
        return [
            "minLength" => $this->minLength
        ];
    }
}
