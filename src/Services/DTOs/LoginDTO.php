<?php

namespace App\Services\DTOs;

class LoginDTO
{
    public function __construct(
        public string $username,
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
