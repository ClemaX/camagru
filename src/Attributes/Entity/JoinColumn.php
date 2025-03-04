<?php

namespace App\Attributes\Entity;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JoinColumn
{
	public function __construct(public readonly string $name)
	{
	}
}
