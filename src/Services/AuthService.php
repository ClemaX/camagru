<?php

namespace App\Services;

use App\Entities\User;
use App\Entities\UserProfile;
use App\Entities\UserSettings;
use App\Enumerations\Role;
use App\Exceptions\AuthLockedException;
use App\Exceptions\ConflictException;
use App\Exceptions\InternalException;
use App\Exceptions\UnauthorizedException;
use App\Repositories\UserRepository;
use App\Services\DTOs\EmailChangeDTO;
use App\Services\DTOs\LoginDTO;
use App\Services\DTOs\PasswordChangeDTO;
use App\Services\DTOs\PasswordResetDTO;
use App\Services\DTOs\PasswordResetRequestDTO;
use App\Services\DTOs\SignupDTO;
use DateInterval;
use DateTime;
use SensitiveParameter;

class AuthService
{
	private readonly string $unlockPath;
	private readonly string $passwordChangePath;
	private readonly string $emailChangeVerifyPath;
	private readonly string $adminEmailAddress;
	private readonly DateInterval $unlockTokenLifetime;

	/**
	 * @param array<string, string> $config
	 */
	public function __construct(
		private readonly UserRepository $userRepository,
		private readonly UserSessionServiceInterface $sessionService,
		private readonly MailService $mailService,
		#[SensitiveParameter] array $config,
	) {
		$this->unlockPath = '/auth/activate';
		$this->passwordChangePath = '/auth/choose-password';
		$this->emailChangeVerifyPath = '/auth/change-email';
		$this->unlockTokenLifetime = DateInterval::createFromDateString(
			$config['USER_UNLOCK_TOKEN_LIFETIME']
		);
		$this->adminEmailAddress = $config['ADMIN_EMAIL'];
	}

	public function signup(#[SensitiveParameter] SignupDTO $dto): void
	{
		if ($this->userRepository->findByUsername($dto->username) != null) {
			throw new ConflictException('username');
		}

		if ($this->userRepository->findByEmailAddress($dto->email) != null) {
			throw new ConflictException('email');
		}

		$passwordHash = password_hash($dto->password, PASSWORD_BCRYPT);

		$unlockToken = bin2hex(random_bytes(32));

		$settings = new UserSettings();
		$profile = new UserProfile();
		$role = ($dto->email === $this->adminEmailAddress) ? Role::ADMIN : Role::USER;

		$user = new User();

		$user->emailAddress = $dto->email;
		$user->username = $dto->username;
		$user->passwordHash = $passwordHash;
		$user->isLocked = true;
		$user->lockedAt = time();
		$user->unlockToken = $unlockToken;
		$user->profile = $profile;
		$user->settings = $settings;
		$user->role = $role;

		$user = $this->userRepository->save($user);

		$activationQueryParams = [
			'id' => $user->id,
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

		$lockedAt = new DateTime();
		$lockedAt->setTimestamp($user->lockedAt);

		$tokenExpiredAt = $lockedAt->add($this->unlockTokenLifetime);

		if ($now >= $tokenExpiredAt
		|| !hash_equals($user->unlockToken, $token)) {
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
	): void {
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

		$lockedAt = new DateTime();
		$lockedAt->setTimestamp($user->lockedAt);

		$tokenExpiredAt = $lockedAt->add($this->unlockTokenLifetime);

		if ($now >= $tokenExpiredAt
		|| !hash_equals($user->unlockToken, $dto->token)) {
			return false;
		}

		$passwordHash = password_hash($dto->password, PASSWORD_BCRYPT);

		$user->passwordHash = $passwordHash;
		$user->isLocked = false;
		$user->lockedAt = null;
		$user->unlockToken = null;

		$this->userRepository->update($user);

		$this->sessionService->login($user);

		return true;
	}

	public function changePassword(
		User $user,
		#[SensitiveParameter] PasswordChangeDTO $dto,
	): void {
		$passwordHash = password_hash($dto->password, PASSWORD_BCRYPT);

		$user->passwordHash = $passwordHash;

		$this->userRepository->update($user);
	}

	public function requestEmailChange(
		User $user,
		#[SensitiveParameter] EmailChangeDTO $dto,
	): void {
		if ($this->userRepository->findByEmailAddress($dto->email) != null) {
			throw new ConflictException('email');
		}

		$user->emailChangeAddress = $dto->email;
		$user->emailChangeRequestedAt = time();
		$user->emailChangeToken = bin2hex(random_bytes(32));

		$this->userRepository->update($user);

		$verifyQueryParams = [
			'id' => $user->id,
			'token' => $user->emailChangeToken,
		];

		$verifyUrl = $this->emailChangeVerifyPath
			. '?' . http_build_query($verifyQueryParams);

		$this->mailService->send(
			$user->emailChangeAddress,
			'Camagru Email Verification',
			'verify-email',
			[
				'username' => $user->username,
				'verifyUrl' => $verifyUrl,
				'urlLifetime' => $this->unlockTokenLifetime->format('%i minutes'),
			]
		);
	}

	public function changeEmail(
		int $userId,
		#[SensitiveParameter] string $token
	): bool {
		$now = new DateTime('now');

		$user = $this->userRepository->findById($userId);

		if ($user === null
		|| $user->emailChangeAddress == null) {
			return false;
		}

		$emailChangeRequestedAt = new DateTime();
		$emailChangeRequestedAt->setTimestamp($user->emailChangeRequestedAt);

		$tokenExpiredAt = $emailChangeRequestedAt->add($this->unlockTokenLifetime);

		if ($now >= $tokenExpiredAt
		|| !hash_equals($user->emailChangeToken, $token)) {
			return false;
		}

		$user->emailAddress = $user->emailChangeAddress;
		$user->emailChangeAddress = null;
		$user->emailChangeRequestedAt = null;
		$user->emailChangeToken = null;

		$this->userRepository->update($user);

		return true;
	}

	public function login(#[SensitiveParameter] LoginDTO $dto): User
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

	public function logout(): void
	{
		$this->sessionService->logout();
	}
}
