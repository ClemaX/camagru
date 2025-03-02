<?php

namespace App\Attributes\Validation;

interface ValidationInterface
{
	public function validate($value): ?string;
	public function getConstraints(): array;
}
