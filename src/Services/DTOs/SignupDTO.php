<?php

namespace App\Services\DTOs;

use App\Attributes\Validation\ValidEmailAddress;
use App\Attributes\Validation\ValidPassword;
use App\Attributes\Validation\ValidUsername;

class SignupDTO
{
	#[ValidUsername()]
	public string $username;
	#[ValidEmailAddress()]
	public string $email;
	#[ValidPassword()]
	public string $password;
}
