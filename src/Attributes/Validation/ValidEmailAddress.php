<?php

namespace App\Attributes\Validation;

use Attribute;

#[Attribute()]
class ValidEmailAddress extends Pattern
{
	public function __construct()
	{
		parent::__construct("/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/");
	}
}
