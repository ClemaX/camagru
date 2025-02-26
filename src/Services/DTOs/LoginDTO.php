<?php

namespace App\Services\DTOs;

use App\Attributes\Validation\ValidPassword;
use App\Attributes\Validation\ValidUsername;

class LoginDTO
{
    public function __construct(
        #[ValidUsername()]
        public string $username,
        #[ValidPassword()]
        public string $password,
    ) {
    }

    public static function load(array $data): LoginDTO
    {
        return new LoginDTO(
            username: $data["username"],
            password: $data["password"],
        );
    }
}
