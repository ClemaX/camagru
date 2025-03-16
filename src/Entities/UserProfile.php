<?php

namespace App\Entities;

use App\Attributes\Entity\Column;
use App\Attributes\Entity\Entity;
use App\Attributes\Entity\Id;
use App\Attributes\Validation\MaxLength;
use App\Attributes\Validation\NotNull;

#[Entity("user_profile")]
class UserProfile
{
	#[Id]
	#[Column("user_id")]
	public int $userId = 0;

	#[NotNull]
	#[MaxLength(140)]
	#[Column("description")]
	public string $description = '';
}
