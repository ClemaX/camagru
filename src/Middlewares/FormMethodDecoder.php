<?php

namespace App\Middlewares;

use App\Request;
use App\Response;
use Closure;

class FormMethodDecoder implements MiddlewareInterface
{
	public function handle(Request $request, Closure $next): Response
	{
		if ($request->method === 'POST'
		&& array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)
		&& $_SERVER['HTTP_CONTENT_TYPE'] === 'application/x-www-form-urlencoded'
		&& array_key_exists('_method', $request->body)) {
			$request->method = $request->body['_method'];
		}

		return $next($request);
	}
}
