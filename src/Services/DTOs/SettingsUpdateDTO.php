<?php

namespace App\Services\DTOs;

use App\Attributes\Validation\NotNull;

require_once __DIR__ . '/../../Attributes/Validation/NotNull.php';

class SettingsUpdateDTO
{
	#[NotNull]
	public bool $commentNotification;
}
