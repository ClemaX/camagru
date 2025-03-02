<?php

namespace App\Entities;

use App\Attributes\Validation\MaxLength;
use App\Attributes\Validation\NotNull;

require_once __DIR__ . '/../Attributes/Validation/MaxLength.php';
require_once __DIR__ . '/../Attributes/Validation/NotNull.php';

class UserProfile
{
	public function __construct(
		public int $userId,
		#[NotNull()]
		#[MaxLength(140)]
		public string $description,
	) {
	}

	public static function load(mixed $data)
	{
		return new UserProfile(
			$data->user_id,
			$data->description
		);
	}
}
