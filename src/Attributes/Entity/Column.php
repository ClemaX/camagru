<?php

namespace App\Attributes\Entity;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
	public function __construct(
		public ?string $name = null,
	) {
	}
}
