<?php

namespace App\Exceptions;

class ForbiddenException extends HttpException
{
	public function __construct()
	{
		parent::__construct(
			403,
			"Forbidden",
			"You are not allowed to view this content.",
			4030
		);
	}
}
