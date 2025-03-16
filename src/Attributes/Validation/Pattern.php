<?php

namespace App\Attributes\Validation;

use Attribute;

#[Attribute()]
class Pattern implements ValidationInterface
{
	public function __construct(private string $pattern)
	{
	}

	public function validate($value): ?string
	{
		if (!preg_match($this->pattern, $value)) {
			return "PATTERN_ERROR";
		}
		return null;
	}

	public function getConstraints(): array
	{
		return [
			"pattern" => $this->pattern,
		];
	}
}
