<?php

namespace App\Repositories;

use App\Entities\UserProfile as UserProfile;

class UserProfileRepository implements AbstractRepository
{
	protected function getModelClass(): string
	{
		return UserProfile::class;
	}
}
