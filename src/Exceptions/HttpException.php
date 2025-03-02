<?php

namespace App\Exceptions;

use Exception;
use Throwable;

abstract class HttpException extends Exception
{
	public function __construct(
		protected int $statusCode,
		protected string $title,
		string $message,
		$code = 0,
		?Throwable $previous = null
	) {
		$this->statusCode = $statusCode;
		$this->title = $title;
		parent::__construct($message, $code, $previous);
	}

	public function getStatusCode()
	{
		return $this->statusCode;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function sendHeaders()
	{
		http_response_code($this->statusCode);
	}
}
