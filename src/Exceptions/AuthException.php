<?php

use App\Exceptions\HttpException;

class AuthException extends HttpException
{
	public function __construct(string $message, int $code = 4010)
	{
		parent::__construct(401, "Authentication Failed", $message, $code);
	}
}
