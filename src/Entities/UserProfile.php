<?php

namespace App\Entities;

use App\Attributes\Entity\Column;
use App\Attributes\Entity\Entity;
use App\Attributes\Entity\Id;
use App\Attributes\Validation\MaxLength;
use App\Attributes\Validation\NotNull;

require_once __DIR__ . '/../Attributes/Entity/Column.php';
require_once __DIR__ . '/../Attributes/Entity/Entity.php';
require_once __DIR__ . '/../Attributes/Entity/Id.php';
require_once __DIR__ . '/../Attributes/Validation/MaxLength.php';
require_once __DIR__ . '/../Attributes/Validation/NotNull.php';

#[Entity("user_profile")]
class UserProfile
{
	#[Id]
	#[Column("user_id")]
	public int $userId = 0;

	#[NotNull]
	#[MaxLength(140)]
	#[Column("description")]
	public string $description;
}
