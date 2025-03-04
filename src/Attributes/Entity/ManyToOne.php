<?php

namespace App\Attributes\Entity;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToOne
{
	public function __construct()
	{
	}
}
