<?php

namespace App\Services;

use App\Entities\User;
use App\Exceptions\ConflictException;
use App\Exceptions\InternalException;
use App\Repositories\UserRepository;
use App\Services\DTOs\ProfileUpdateDTO;
use App\Services\DTOs\SettingsUpdateDTO;

require_once __DIR__ . '/../Entities/User.php';
require_once __DIR__ . '/../Repositories/UserRepository.php';

class UserService
{
	public function __construct(private UserRepository $userRepository)
	{
	}

	public function updateProfile(User $user, ProfileUpdateDTO $dto): User
	{
		if ($dto->username !== $user->username
		&& $this->userRepository->findByUsername($dto->username) !== null) {
			throw new ConflictException('username');
		}

		$user->username = $dto->username;
		$user->profile->description = $dto->description;

		if (!$this->userRepository->update($user)) {
			throw new InternalException();
		}

		return $user;
	}

	public function updateSettings(User $user, SettingsUpdateDTO $dto): User
	{
		$user->settings->commentNotification = $dto->commentNotification;

		if (!$this->userRepository->update($user)) {
			throw new InternalException();
		}

		return $user;
	}
}
