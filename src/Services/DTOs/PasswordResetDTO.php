<?php

namespace App\Services\DTOs;

use App\Attributes\Validation\NotNull;
use App\Attributes\Validation\ValidPassword;

class PasswordResetDTO
{
	#[NotNull]
	public int $userId;
	#[NotNull]
	public string $token;
	#[ValidPassword]
	public string $password;
}
