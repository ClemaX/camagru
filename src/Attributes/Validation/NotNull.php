<?php

namespace App\Attributes\Validation;

use Attribute;

require_once __DIR__ . '/ValidationInterface.php';

#[Attribute()]
class NotNull implements ValidationInterface
{
    public function __construct()
    {
    }

    public function validate($value): ?string
    {
        if ($value == null) {
            return "NOT_NULL_ERROR";
        }
        return null;
    }

    public function getConstraints(): array
    {
        return [];
    }
}
