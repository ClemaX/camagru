<?php

namespace App\Services\DTOs;

use App\Attributes\Validation\ValidPassword;

require_once __DIR__ . '/../../Attributes/Validation/ValidPassword.php';

class PasswordChangeDTO
{
	#[ValidPassword]
	public string $password;
}
