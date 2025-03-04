<?php

namespace App\Services\DTOs;

use App\Attributes\Validation\MaxLength;
use App\Attributes\Validation\MinLength;
use App\Attributes\Validation\NotBlank;
use App\Attributes\Validation\NotNull;

require_once __DIR__ . '/../../Attributes/Validation/MaxLength.php';
require_once __DIR__ . '/../../Attributes/Validation/NotBlank.php';
require_once __DIR__ . '/../../Attributes/Validation/NotNull.php';

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
