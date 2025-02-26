<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class CurrentUser
{
    public function __construct()
    {
    }
}
