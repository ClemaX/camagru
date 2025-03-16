<?php

namespace App\Attributes\Validation;

interface ValidationInterface
{
	public function validate(mixed $value): ?string;

	/**
	 * @return array<string, mixed>
	 */
	public function getConstraints(): array;
}
