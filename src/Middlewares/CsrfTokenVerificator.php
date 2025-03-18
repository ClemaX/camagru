<?php

namespace App\Middlewares;

use App\Exceptions\InvalidCsrfTokenException;
use App\Request;
use App\Response;
use App\Services\UserSessionServiceInterface;
use Closure;

class CsrfTokenVerificator implements MiddlewareInterface
{
	/**
	 * @param string[] $protectedMethods
	 */
	public function __construct(
		private readonly UserSessionServiceInterface $sessionService,
		private readonly array $protectedMethods = ['POST', 'PUT', 'PATCH'],
	) {
	}

	public function handle(Request $request, Closure $next): Response
	{
		if (in_array($request->method, $this->protectedMethods)
		&& ($request->body === null
		|| !array_key_exists('_token', $request->body)
		|| !$this->sessionService->verifyCsrfToken($request->body['_token']))) {
			throw new InvalidCsrfTokenException();
		}

		return $next($request);
	}
}
