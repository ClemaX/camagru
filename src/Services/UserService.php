<?php

namespace App\Services;

use App\Entities\User;
use App\Exceptions\ConflictException;
use App\Repositories\UserRepository;
use App\Services\DTOs\ProfileUpdateDTO;
use App\Services\DTOs\SettingsUpdateDTO;

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

		$this->userRepository->update($user);

		return $user;
	}

	public function updateSettings(User $user, SettingsUpdateDTO $dto): User
	{
		$user->settings->commentNotification = $dto->commentNotification;

		$this->userRepository->update($user);

		return $user;
	}
}
