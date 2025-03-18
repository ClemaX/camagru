<?php

namespace App\Exceptions;

class NotFoundException extends HttpException
{
	public function __construct(string $message = "Entity not found")
	{
		parent::__construct(
			404,
			"Not Found",
			$message,
			4040
		);
	}
}
