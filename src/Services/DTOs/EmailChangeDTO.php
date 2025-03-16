<?php

namespace App\Services\DTOs;

use App\Attributes\Validation\ValidEmailAddress;

class EmailChangeDTO
{
	#[ValidEmailAddress]
	public string $email;
}
