<?php

namespace App\Exceptions;

use Exception;

class ConflictException extends HttpException
{
	protected string $field;

	public function __construct(
		string $field,
		string $message = 'The entity already exists',
		int $code = 409
	) {
		parent::__construct($code, 'Conflict', $message);
		$this->field = $field;
	}

	public function getField(): string
	{
		return $this->field;
	}
}
