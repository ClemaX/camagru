<?php

namespace App\Attributes\Validation;

use Attribute;

#[Attribute()]
class MinLength implements ValidationInterface
{
	public function __construct(private int $minLength)
	{
	}

	public function validate($value): ?string
	{
		if (mb_strlen($value) < $this->minLength) {
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
