<?php

namespace App\Repositories;

use App\Entities\UserProfile as UserProfile;

/** @extends AbstractRepository<UserProfile> */
class UserProfileRepository extends AbstractRepository
{
	protected function getModelClass(): string
	{
		return UserProfile::class;
	}
}
