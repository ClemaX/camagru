<?php

namespace App\Services;

use App\Entities\User;
use App\Exceptions\ConflictException;
use App\Exceptions\InternalException;
use App\Exceptions\UnauthorizedException;
use App\Repositories\UserRepository;
use App\Services\DTOs\LoginDTO;
use App\Services\DTOs\SignupDTO;
use DateInterval;
use DateTime;
use SensitiveParameter;

require_once __DIR__ . '/../Enumerations/Role.php';
require_once __DIR__ . '/../Exceptions/UnauthorizedException.php';
require_once __DIR__ . '/../Repositories/UserRepository.php';
require_once __DIR__ . '/../Services/MailService.php';
require_once __DIR__ . '/../Services/UserSessionServiceInterface.php';
require_once __DIR__ . '/DTOs/LoginDTO.php';
require_once __DIR__ . '/DTOs/SignupDTO.php';

class AuthService
{
    private readonly string $unlockPath;
    private readonly DateInterval $unlockTokenLifetime;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserSessionServiceInterface $sessionService,
        private readonly MailService $mailService,
        #[SensitiveParameter] array $config,
    ) {
        $this->unlockPath = '/auth/activate';
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

        $activationUrl = $this->unlockPath
            . '?' . http_build_query($activationQueryParams);

        $this->mailService->send(
            $user->emailAddress,
            'Welcome to Camagru',
            'activate-account',
            [
                'username' => $user->username,
                'activationUrl' => $activationUrl,
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

    public function login(#[SensitiveParameter] LoginDTO $dto)
    {
        $user = $this->userRepository->findByUsername($dto->username);

        if ($user === null || $user->isLocked
        || !password_verify($dto->password, $user->passwordHash)) {
            throw new UnauthorizedException();
        }

        $this->sessionService->setUser($user);

        return $user;
    }
}
