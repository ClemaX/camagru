<?php

namespace App\Services\DTOs;

use App\Attributes\Validation\MaxLength;
use App\Attributes\Validation\ValidUsername;

require_once __DIR__ . '/../../Attributes/Validation/MaxLength.php';
require_once __DIR__ . '/../../Attributes/Validation/ValidUsername.php';

class ProfileUpdateDTO
{
	#[ValidUsername]
	public string $username;

	#[MaxLength(140)]
	public string $description;
}
