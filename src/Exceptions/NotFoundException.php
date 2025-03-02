<?php

namespace App\Exceptions;

require_once __DIR__ . '/HttpException.php';

class NotFoundException extends HttpException
{
	public function __construct()
	{
		parent::__construct(
			404,
			"Page Not Found",
			"This is not the web page you are looking for.",
			4040
		);
	}
}
