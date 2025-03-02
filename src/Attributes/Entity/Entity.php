<?php

namespace App\Attributes\Entity;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Entity
{
	public function __construct(
		public readonly string $tableName,
	) {
	}
}
