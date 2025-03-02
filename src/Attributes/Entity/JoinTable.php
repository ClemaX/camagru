<?php

namespace App\Attributes\Entity;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JoinTable
{
	public function __construct(
		public readonly string $tableName,
		public readonly ?string $joinColumn,
		public readonly ?string $reverseJoinColumn,
	) {
	}
}
