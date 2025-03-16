<?php

namespace App\Entities;

use App\Attributes\Entity\Column;
use App\Attributes\Entity\Entity;
use App\Attributes\Entity\Id;
use App\Attributes\Validation\MaxLength;
use App\Attributes\Validation\NotNull;

#[Entity("user_settings")]
class UserSettings
{
	#[Id]
	#[Column("user_id")]
	public int $userId = 0;

	#[Column("comment_notification")]
	public bool $commentNotification = true;
}
