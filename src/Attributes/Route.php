<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
	public function __construct(
		public readonly string $path,
		public readonly string $method = 'GET'
	) {
	}
}
