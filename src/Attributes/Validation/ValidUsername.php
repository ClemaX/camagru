<?php

namespace App\Attributes\Validation;

use Attribute;

#[Attribute()]
class ValidUsername extends Pattern
{
	public function __construct()
	{
		parent::__construct("/^[a-zA-Z0-9_\-]{3,16}$/");
	}
}
