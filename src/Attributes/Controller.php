<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
	public function __construct(
		public readonly string $path,
	) {
	}
}
