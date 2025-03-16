<?php

namespace App\Services\DTOs;

use App\Attributes\Validation\MaxLength;
use App\Attributes\Validation\MinLength;
use App\Attributes\Validation\NotBlank;
use App\Attributes\Validation\NotNull;

class PostCreationDTO
{
	#[NotNull]
	#[NotBlank]
	#[MinLength(3)]
	#[MaxLength(64)]
	public string $title;

	#[NotNull]
	#[MaxLength(512)]
	public string $description;
}
