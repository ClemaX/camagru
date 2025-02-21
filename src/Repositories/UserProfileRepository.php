<?php

namespace App\Repositories;

use App\Entities\UserProfile as UserProfile;

require_once __DIR__ . '/AbstractRepository.php';

class UserProfileRepository implements AbstractRepository
{
    protected function getTableName(): string
    {
        return 'user_profile';
    }

    protected function getModelClass(): string
    {
        return UserProfile::class;
    }
}
