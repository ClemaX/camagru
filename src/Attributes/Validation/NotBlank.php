<?php

namespace App\Attributes\Validation;

use Attribute;

#[Attribute()]
class NotBlank implements ValidationInterface
{
	public function __construct()
	{
	}

	public function validate($value): ?string
	{
		if (ctype_space($value)) {
			return "NOT_BLANK_ERROR";
		}
		return null;
	}

	public function getConstraints(): array
	{
		return [];
	}
}
