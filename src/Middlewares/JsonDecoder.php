<?php

namespace App\Middlewares;

use App\Request;
use App\Response;
use Closure;

class JsonDecoder implements MiddlewareInterface
{
	public function handle(Request $request, Closure $next): Response
	{
		if ($request->contentType === 'application/json') {
			$request->body = json_decode(
				file_get_contents('php://input'),
				associative: true
			);
		}

		return $next($request);
	}
}
