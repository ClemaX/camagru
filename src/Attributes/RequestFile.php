<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class RequestFile
{
	public function __construct(private string $name)
	{
	}

	public function getName(): string
	{
		return $this->name;
	}
}
