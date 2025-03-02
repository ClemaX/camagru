<?php

namespace App\Attributes\Validation;

use Attribute;

require_once __DIR__ . '/ValidationInterface.php';

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
