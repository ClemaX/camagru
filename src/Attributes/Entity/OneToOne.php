<?php

namespace App\Attributes\Entity;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class OneToOne
{
	public function __construct()
	{
	}
}
