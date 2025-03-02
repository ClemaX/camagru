<?php

namespace App\Services\DTOs;

use App\Attributes\Validation\NotNull;
use App\Attributes\Validation\ValidPassword;

require_once __DIR__ . '/../../Attributes/Validation/NotNull.php';
require_once __DIR__ . '/../../Attributes/Validation/ValidPassword.php';

class PasswordResetDTO
{
	#[NotNull]
	public int $userId;
	#[NotNull]
	public string $token;
	#[ValidPassword]
	public string $password;
}
