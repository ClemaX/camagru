<?php

namespace App\Middlewares;

use App\Request;
use App\Response;
use Closure;

interface MiddlewareInterface
{
	public function handle(Request $request, Closure $next): Response;
}
