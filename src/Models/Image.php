<?php

namespace App\Model;

class Image
{
    public function __construct(
        public readonly string $url,
        public readonly string $title,
        public readonly string $description,
    ) {
    }
}
