<?php

namespace App\Attributes\Validation;

use Attribute;

function mb_nextchar(string $string, int &$pointer): string | false
{
	if (!isset($string[$pointer])) {
		return false;
	}
	$char = ord($string[$pointer]);
	if ($char < 128) {
		return $string[$pointer++];
	} else {
		if ($char < 224) {
			$bytes = 2;
		} elseif ($char < 240) {
			$bytes = 3;
		} else {
			$bytes = 4;
		}
		$str = substr($string, $pointer, $bytes);
		$pointer += $bytes;
		return $str;
	}
}

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

	public function validate(mixed $value): ?string
	{
		$error = parent::validate($value);

		if ($error != null) {
			return $error;
		}

		$lowercase = $uppercase = $numeric = $special = 0;
		$pointer = 0;
		while (($char = mb_nextchar($value, $pointer)) !== false) {
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
