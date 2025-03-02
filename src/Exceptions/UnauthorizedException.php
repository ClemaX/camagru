<?php

namespace App\Exceptions;

require_once __DIR__ . '/AuthException.php';

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
