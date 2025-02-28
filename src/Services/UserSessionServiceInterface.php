<?php

namespace App\Services;

use App\Entities\User;

require_once __DIR__ . '/../Entities/User.php';

interface UserSessionServiceInterface
{
    public function setUser(?User $user);
    public function getUser(): ?User;
}
