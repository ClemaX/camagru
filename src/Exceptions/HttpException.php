<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use JsonSerializable;

abstract class HttpException extends Exception implements JsonSerializable
{
	protected ?string $type = null;
	protected ?string $instance = null;

	/**
	 * @param array<string, mixed> $extensions
	 */
	public function __construct(
		protected int $statusCode,
		protected string $title,
		string $message,
		int $code = 0,
		?Throwable $previous = null,
		protected ?array $extensions = null
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

	public function setType(string $type): self
	{
		$this->type = $type;
		return $this;
	}

	public function setInstance(string $instance): self
	{
		$this->instance = $instance;
		return $this;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getProblemDetails(): array
	{
		$problemDetails = [
			'status' => $this->statusCode,
			'title' => $this->title,
			'detail' => $this->getMessage(),
		];

		if ($this->type !== null) {
			$problemDetails['type'] = $this->type;
		}

		if ($this->instance !== null) {
			$problemDetails['instance'] = $this->instance;
		}

		if ($this->extensions !== null) {
			$problemDetails = array_merge($problemDetails, $this->extensions);
		}

		return $problemDetails;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function jsonSerialize(): array
	{
		return $this->getProblemDetails();
	}
}
