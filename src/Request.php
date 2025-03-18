<?php

namespace App;

class Request
{
	public string $path;

	/** @var array<string, string|array<string|int, string>> $params */
	public array $params;

	/**
	 * @param ?array<string, string> $body
	 */
	public function __construct(
		string $uri,
		public string $method,
		public ?string $accept,
		public ?string $contentType,
		public ?array $body,
	) {
		$urlParts = parse_url($uri);

		$this->path = $urlParts['path'];
		$this->params = [];

		if (array_key_exists('query', $urlParts)) {
			parse_str($urlParts['query'], $this->params);
		}
	}
}
