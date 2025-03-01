<?php

namespace App\Services\DTOs;

class PasswordResetDTO
{
    public int $userId;
    public string $token;
    public string $password;
}
