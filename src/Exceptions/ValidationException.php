<?php

namespace App\Exceptions;

class ValidationException extends HttpException
{
	public function __construct(
		protected array $errors,
		int $code = 4001
	) {
		parent::__construct(
			400,
			"Validation Failed",
			"One or more of the supplied fields are invalid.",
			$code
		);

		$this->errors = $errors;
	}

	public function getErrors(): array
	{
		return $this->errors;
	}
}
