<?php

namespace App\Exceptions;

use Exception;

class InternalException extends HttpException
{
	public function __construct(
		string $message = "The server was not able to process the request",
		int $code = 4090
	) {
		parent::__construct(409, "Internal Server Error", $message, $code);
	}
}
