<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class RequestParam
{
    public function __construct(private string | null $name = null)
    {
    }

    public function getName(): string | null
    {
        return $this->name;
    }
}
