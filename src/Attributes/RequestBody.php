<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class RequestBody
{
	public function __construct()
	{
	}
}
