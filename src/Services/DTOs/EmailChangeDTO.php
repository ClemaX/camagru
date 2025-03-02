<?php

namespace App\Services\DTOs;

use App\Attributes\Validation\ValidEmailAddress;

require_once __DIR__ . '/../../Attributes/Validation/ValidEmailAddress.php';

class EmailChangeDTO
{
	#[ValidEmailAddress]
	public string $email;
}
