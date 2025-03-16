<?php

namespace App\Attributes\Validation;

use Attribute;

#[Attribute()]
class NotNull implements ValidationInterface
{
	public function __construct()
	{
	}

	public function validate(mixed $value): ?string
	{
		if ($value === null) {
			return "NOT_NULL_ERROR";
		}
		return null;
	}

	public function getConstraints(): array
	{
		return [];
	}
}
