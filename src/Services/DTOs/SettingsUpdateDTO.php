<?php

namespace App\Services\DTOs;

use App\Attributes\Validation\NotNull;

class SettingsUpdateDTO
{
	#[NotNull]
	public bool $commentNotification;
}
