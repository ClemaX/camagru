<?php

namespace App\Services;

use App\Entities\User;
use App\Repositories\UserRepository;

require_once __DIR__ . '/../Entities/User.php';
require_once __DIR__ . '/../Repositories/UserRepository.php';

class UserService
{
	public function __construct(private UserRepository $userRepository)
	{
	}
}
