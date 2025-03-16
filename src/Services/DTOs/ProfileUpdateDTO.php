<?php

namespace App\Services\DTOs;

use App\Attributes\Validation\MaxLength;
use App\Attributes\Validation\ValidUsername;

class ProfileUpdateDTO
{
	#[ValidUsername]
	public string $username;

	#[MaxLength(140)]
	public string $description;
}
