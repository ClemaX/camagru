<?php

namespace App\Attributes\Entity;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Id
{
	public function __construct()
	{
	}
}
