<?php

namespace App\Middlewares;

use App\Exceptions\HttpException;
use App\Renderer;
use App\Request;
use App\Response;
use Closure;

class ExceptionHandler implements MiddlewareInterface
{
	public function __construct(private readonly Renderer $renderer)
	{
	}

	public function handle(Request $request, Closure $next): Response
	{
		try {
			$response = $next($request);
		} catch (HttpException $e) {
			if ($request->accept === 'application/json') {
				$response = Response::json(
					$e,
					$e->getStatusCode(),
					contentType: 'application/problem+json; charset=utf-8'
				);
			} else {
				$content = $this->renderer->render('error', [
					'title' => $e->getTitle(),
					'message' => $e->getMessage(),
					'code' => $e->getCode(),
				]);

				$content = $this->renderer->render('layout', [
					"content" => $content,
				]);

				$response = new Response($content, $e->getStatusCode());
			}
		}

		return $response;
	}
}
