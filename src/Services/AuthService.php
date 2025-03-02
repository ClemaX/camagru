<?php

namespace App\Services;

use App\Entities\User;
use App\Exceptions\ConflictException;
use App\Exceptions\InternalException;
use App\Exceptions\UnauthorizedException;
use App\Repositories\UserRepository;
use App\Services\DTOs\LoginDTO;
use App\Services\DTOs\PasswordResetDTO;
use App\Services\DTOs\PasswordResetRequestDTO;
use App\Services\DTOs\SignupDTO;
use AuthLockedException;
use DateInterval;
use DateTime;
use SensitiveParameter;

require_once __DIR__ . '/../Enumerations/Role.php';
require_once __DIR__ . '/../Exceptions/AuthLockedException.php';
require_once __DIR__ . '/../Exceptions/UnauthorizedException.php';
require_once __DIR__ . '/../Repositories/UserRepository.php';
require_once __DIR__ . '/../Services/MailService.php';
require_once __DIR__ . '/../Services/UserSessionServiceInterface.php';
require_once __DIR__ . '/DTOs/LoginDTO.php';
require_once __DIR__ . '/DTOs/SignupDTO.php';
require_once __DIR__ . '/DTOs/PasswordResetDTO.php';
require_once __DIR__ . '/DTOs/PasswordResetRequestDTO.php';

class AuthService
{
	private readonly string $unlockPath;
	private readonly string $passwordChangePath;
	private readonly DateInterval $unlockTokenLifetime;

	public function __construct(
		private readonly UserRepository $userRepository,
		private readonly UserSessionServiceInterface $sessionService,
		private readonly MailService $mailService,
		#[SensitiveParameter] array $config,
	) {
		$this->unlockPath = '/auth/activate';
		$this->passwordChangePath = '/auth/choose-password';
		$this->unlockTokenLifetime = DateInterval::createFromDateString(
			$config['USER_UNLOCK_TOKEN_LIFETIME']
		);
	}

	public function signup(#[SensitiveParameter] SignupDTO $dto)
	{
		if ($this->userRepository->findByUsername($dto->username) != null) {
			throw new ConflictException('username');
		}

		if ($this->userRepository->findByEmailAddress($dto->email) != null) {
			throw new ConflictException('email');
		}

		$passwordHash = password_hash($dto->password, PASSWORD_BCRYPT);

		if ($passwordHash === false) {
			throw new InternalException();
		}

		$unlockToken = bin2hex(random_bytes(32));

		$user = new User();

		$user->emailAddress = $dto->email;
		$user->username = $dto->username;
		$user->passwordHash = $passwordHash;
		$user->isLocked = true;
		$user->lockedAt = time();
		$user->unlockToken = $unlockToken;

		$userId = $this->userRepository->save($user);

		$activationQueryParams = [
			'id' => $userId,
			'token' => $unlockToken,
		];

		$activationUrl = $this->unlockPath
			. '?' . http_build_query($activationQueryParams);

		$this->mailService->send(
			$user->emailAddress,
			'Welcome to Camagru',
			'activate-account',
			[
				'username' => $user->username,
				'activationUrl' => $activationUrl,
				'urlLifetime' => $this->unlockTokenLifetime->format('%i minutes'),
			]
		);
	}

	public function activate(
		int $userId,
		#[SensitiveParameter] string $token
	): bool {
		$now = new DateTime('now');

		$user = $this->userRepository->findById($userId);

		if ($user === null
		|| !$user->isLocked || $user->passwordHash === null) {
			return false;
		}

		$lockedAt = DateTime::createFromFormat('U', $user->lockedAt);
		$tokenExpiredAt = $lockedAt->add($this->unlockTokenLifetime);

		if ($now >= $tokenExpiredAt
		|| strcmp($user->unlockToken, $token) != 0) {
			return false;
		}

		$user->isLocked = false;
		$user->lockedAt = null;
		$user->unlockToken = null;

		$this->userRepository->update($user);

		return true;
	}

	public function requestPasswordReset(
		#[SensitiveParameter] PasswordResetRequestDTO $dto
	) {
		$user = $this->userRepository->findByEmailAddress($dto->email);

		if ($user === null) {
			return;
		}

		$user->isLocked = true;
		$user->lockedAt = time();
		$user->unlockToken = bin2hex(random_bytes(32));
		$user->passwordHash = null;

		$this->userRepository->update($user);

		$activationQueryParams = [
			'id' => $user->id,
			'token' => $user->unlockToken,
		];

		$resetUrl = $this->passwordChangePath
			. '?' . http_build_query($activationQueryParams);

		$this->mailService->send(
			$user->emailAddress,
			'Camagru Password Reset',
			'reset-password',
			[
				'username' => $user->username,
				'resetUrl' => $resetUrl,
				'urlLifetime' => $this->unlockTokenLifetime->format('%i minutes'),
			]
		);
	}

	public function resetPassword(
		#[SensitiveParameter] PasswordResetDTO $dto
	): bool {
		$now = new DateTime('now');

		$user = $this->userRepository->findById($dto->userId);

		if ($user === null
		|| !$user->isLocked || $user->passwordHash !== null) {
			return false;
		}

		$lockedAt = DateTime::createFromFormat('U', $user->lockedAt);
		$tokenExpiredAt = $lockedAt->add($this->unlockTokenLifetime);

		if ($now >= $tokenExpiredAt
		|| strcmp($user->unlockToken, $dto->token) != 0) {
			return false;
		}

		$passwordHash = password_hash($dto->password, PASSWORD_BCRYPT);

		if ($passwordHash === false) {
			throw new InternalException();
		}

		$user->passwordHash = $passwordHash;
		$user->isLocked = false;
		$user->lockedAt = null;
		$user->unlockToken = null;

		$this->userRepository->update($user);

		$this->sessionService->login($user);

		return true;
	}

	public function login(#[SensitiveParameter] LoginDTO $dto)
	{
		$user = $this->userRepository->findByUsername($dto->username);

		if ($user === null
		|| !password_verify($dto->password, $user->passwordHash)) {
			throw new UnauthorizedException();
		}

		if ($user->isLocked) {
			throw new AuthLockedException();
		}

		$this->sessionService->login($user);

		return $user;
	}

	public function logout()
	{
		$this->sessionService->logout();
	}
}
