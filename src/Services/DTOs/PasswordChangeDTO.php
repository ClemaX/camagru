<?php

namespace App\Services\DTOs;

use App\Attributes\Validation\ValidPassword;

class PasswordChangeDTO
{
	#[ValidPassword]
	public string $password;
}
