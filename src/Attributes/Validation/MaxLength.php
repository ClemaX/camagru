<?php

namespace App\Attributes\Validation;

use Attribute;

#[Attribute()]
class MaxLength implements ValidationInterface
{
	public function __construct(private int $maxLength)
	{
	}

	public function validate(mixed $value): ?string
	{
		if (!is_string($value) || mb_strlen($value) > $this->maxLength) {
			return "MAX_LENGTH_ERROR";
		}
		return null;
	}

	public function getConstraints(): array
	{
		return [
			"maxLength" => $this->maxLength
		];
	}
}
