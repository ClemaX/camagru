<?php

namespace App\Attributes\Validation;

use Attribute;

#[Attribute()]
class Pattern implements ValidationInterface
{
	public function __construct(private string $pattern)
	{
	}

	public function validate(mixed $value): ?string
	{
		if (!is_string($value) || !preg_match($this->pattern, $value)) {
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
