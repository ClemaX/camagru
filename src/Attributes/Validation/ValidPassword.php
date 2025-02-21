<?php

namespace App\Attributes\Validation;

use Attribute;

require_once __DIR__ . '/ValidationInterface.php';

#[Attribute()]
class ValidPassword extends MinLength
{
    public function __construct(
        int $minLength = 8,
        private int $minLowercase = 1,
        private int $minUppercase = 1,
        private int $minNumeric = 1,
        private int $minSpecial = 1
    ) {
        parent::__construct($minLength);
    }

    public function validate($value): ?string
    {
        $error = parent::validate($value);

        if ($error != null) {
            return $error;
        }

        $lowercase = $uppercase = $numeric = $special = 0;

        for ($i = 0; $i < strlen($value); $i++) {
            $char = $value[$i];
            if (ctype_lower($char)) {
                $lowercase++;
            } elseif (ctype_upper($char)) {
                $uppercase++;
            } elseif (ctype_digit($char)) {
                $numeric++;
            } elseif (ctype_punct($char) || ctype_space($char)) {
                $special++;
            }
        }

        if ($lowercase < $this->minLowercase) {
            return "MIN_LOWERCASE_ERROR";
        }
        if ($uppercase < $this->minUppercase) {
            return "MIN_UPPERCASE_ERROR";
        }
        if ($numeric < $this->minNumeric) {
            return "MIN_NUMERIC_ERROR";
        }
        if ($special < $this->minSpecial) {
            return "MIN_SPECIAL_ERROR";
        }

        return null;
    }

    public function getConstraints(): array
    {
        return [
            ...parent::getConstraints(),
            "minLowercase" => $this->minLowercase,
            "minUppercase" => $this->minUppercase,
            "minNumeric" => $this->minNumeric,
            "minSpecial" => $this->minSpecial,
        ];
    }
}
