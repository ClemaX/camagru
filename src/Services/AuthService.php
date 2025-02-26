<?php

namespace App\Services;

use App\Entities\User;
use App\Exceptions\ConflictException;
use App\Exceptions\InternalException;
use App\Exceptions\UnauthorizedException;
use App\Renderer;
use App\Repositories\UserRepository;
use App\Services\DTOs\LoginDTO;
use App\Services\DTOs\SignupDTO;
use DateInterval;
use DateTime;
use Role;
use SensitiveParameter;

require_once __DIR__ . '/../Enumerations/Role.php';
require_once __DIR__ . '/../Repositories/UserRepository.php';
require_once __DIR__ . '/../Exceptions/UnauthorizedException.php';
require_once __DIR__ . '/DTOs/SignupDTO.php';
require_once __DIR__ . '/DTOs/LoginDTO.php';

define('SESSION_USER_ID_KEY', 'user_id');
define('SESSION_USER_ROLE_KEY', 'user_role');

class AuthService
{
    private readonly string $unlockUrl;
    private readonly DateInterval $unlockTokenLifetime;

    public function __construct(private UserRepository $userRepository, #[SensitiveParameter] private array $config)
    {
        $this->unlockUrl = $config['EXTERNAL_URL'] . '/auth/activate';
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

        $user = new User(
            emailAddress: $dto->email,
            username: $dto->username,
            passwordHash: $passwordHash,
            isLocked: true,
            lockedAt: time(),
            unlockToken: $unlockToken,
        );

        $userId = $this->userRepository->save($user);

        $activationQueryParams = [
            'id' => $userId,
            'token' => $unlockToken,
        ];

        $activationUrl = $this->unlockUrl
            . '?' . http_build_query($activationQueryParams);

        $mailBody = Renderer::render('Mails/activate-account', [
            'username' => $user->username,
            'activationUrl' => $activationUrl,
        ]);

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        mail($user->emailAddress, 'Welcome to Camagru', $mailBody, $headers);
    }

    public function activate(int $userId, #[SensitiveParameter] string $token): bool
    {
        $now = new DateTime('now');

        $user = $this->userRepository->findById($userId);

        if ($user === null || !$user->isLocked || $user->passwordHash === null) {
            return false;
        }

        $lockedAt = DateTime::createFromFormat('U', $user->lockedAt);
        $tokenExpiredAt = $lockedAt->add($this->unlockTokenLifetime);

        if ($now >= $tokenExpiredAt || strcmp($user->unlockToken, $token) != 0) {
            return false;
        }

        $user->isLocked = false;
        $user->lockedAt = null;
        $user->unlockToken = null;

        $this->userRepository->update($user);

        return true;
    }

    public function login(#[SensitiveParameter] LoginDTO $dto) {
        $user = $this->userRepository->findByUsername($dto->username);

        if ($user === null || $user->isLocked
        || !password_verify($dto->password, $user->passwordHash)) {
			throw new UnauthorizedException();
        }

        session_regenerate_id();

        $_SESSION[SESSION_USER_ID_KEY] = $user->id;
        $_SESSION[SESSION_USER_ROLE_KEY] = Role::USER;

		return $user;
    }

	public function getCurrentUser(): ?User {
		if (!array_key_exists(SESSION_USER_ID_KEY, $_SESSION)) {
			return null;
		}

		$userId = $_SESSION[SESSION_USER_ID_KEY];

		return $this->userRepository->findById($userId);
	}
}
