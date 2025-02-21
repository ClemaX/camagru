<?php

namespace App\Entities;

use App\Attributes\Validation\MaxLength;
use App\Attributes\Validation\NotNull;

class UserProfile
{
    public function __construct(
        public int $userId,
        #[NotNull()]
        #[MaxLength(140)]
        public string $description,
    ) {
    }

    public static function load(mixed $data)
    {
        return new UserProfile(
            $data->user_id,
            $data->description
        );
    }
}
