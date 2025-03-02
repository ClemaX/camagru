<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class RequestParam
{
	public function __construct(private ?string $name = null)
	{
	}

	public function getName(): ?string
	{
		return $this->name;
	}
}
