<?php

namespace App;

class Response
{
	public function __construct(
		public readonly ?string $body = null,
		public readonly int $statusCode = 200,
		public readonly string $contentType = 'text/html; charset=utf-8',
		public readonly ?string $location = null,
	) {
	}

	public function send(): void
	{
		http_response_code($this->statusCode);

		header('Content-Type: ' . $this->contentType);

		if ($this->location !== null) {
			header('Location: ' . $this->location);
		}

		if ($this->body !== null) {
			echo $this->body;
		}
	}

	public static function redirect(
		string $location,
		int $statusCode = 302,
	): Response {
		return new Response(location: $location, statusCode: $statusCode);
	}

	/**
	 * @param mixed $body
	 */
	public static function json(
		mixed $body,
		int $statusCode = 200,
		?string $location = null,
		?string $contentType = 'application/json; charset=utf-8',
	): Response {
		return new Response(
			body: json_encode($body),
			statusCode: $statusCode,
			contentType: $contentType,
			location: $location,
		);
	}
}
