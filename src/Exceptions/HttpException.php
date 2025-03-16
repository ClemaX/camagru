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
		int $code = 0,
		?Throwable $previous = null
	) {
		$this->statusCode = $statusCode;
		$this->title = $title;
		parent::__construct($message, $code, $previous);
	}

	public function getStatusCode(): int
	{
		return $this->statusCode;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function sendHeaders(): void
	{
		http_response_code($this->statusCode);
	}
}
