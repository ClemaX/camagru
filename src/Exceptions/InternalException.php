<?php

namespace App\Exceptions;

use Exception;

class InternalException extends HttpException
{
	public function __construct(
		string $message = "The server was not able to process the request",
		int $code = 5000
	) {
		parent::__construct(500, "Internal Server Error", $message, $code);
	}
}
