<?php

namespace App\Services\DTOs;

use App\Attributes\Validation\MaxLength;
use App\Attributes\Validation\MinLength;
use App\Attributes\Validation\NotBlank;
use App\Attributes\Validation\NotNull;

class PostCommentUpdateDTO
{
	#[NotNull]
	#[NotBlank]
	#[MinLength(1)]
	#[MaxLength(512)]
	public string $body;
}
