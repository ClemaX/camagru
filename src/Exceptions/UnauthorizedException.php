<?php

namespace App\Exceptions;

use AuthException;

class UnauthorizedException extends AuthException
{
	public function __construct(
		string $message = 'Invalid credentials. Please try again.',
		int $code = 4010
	) {
		parent::__construct($message, $code);
	}
}
