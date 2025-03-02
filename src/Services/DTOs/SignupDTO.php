<?php

namespace App\Services\DTOs;

use App\Attributes\Validation\ValidEmailAddress;
use App\Attributes\Validation\ValidPassword;
use App\Attributes\Validation\ValidUsername;

require_once __DIR__ . '/../../Attributes/Validation/ValidEmailAddress.php';
require_once __DIR__ . '/../../Attributes/Validation/ValidPassword.php';
require_once __DIR__ . '/../../Attributes/Validation/ValidUsername.php';

class SignupDTO
{
	#[ValidUsername()]
	public string $username;
	#[ValidEmailAddress()]
	public string $email;
	#[ValidPassword()]
	public string $password;
}
