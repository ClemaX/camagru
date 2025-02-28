<?php

namespace App\Services;

use PDO;
use SessionHandlerInterface;
use App\Entities\User;
use App\Enumerations\Role;
use App\Repositories\UserRepository;

require_once __DIR__ . '/UserSessionServiceInterface.php';

define('SESSION_USER_ID_KEY', 'user_id');
define('SESSION_USER_ROLE_KEY', 'user_role');

class DatabaseSessionService implements SessionHandlerInterface, UserSessionServiceInterface
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly UserRepository $userRepository
    ) {
    }

    public function open(string $savePath, string $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $stmt = $this->pdo->prepare("SELECT data FROM session WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['data'] : '';
    }

    public function write(string $id, string $data): bool
    {
        $stmt = $this->pdo->prepare("INSERT INTO session (id, data) VALUES (?, ?) ON CONFLICT (id) DO UPDATE SET data = EXCLUDED.data");
        return $stmt->execute([$id, $data]);
    }

    public function destroy(string $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM session WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function gc(int $maxlifetime): int|false
    {
        $stmt = $this->pdo->prepare("DELETE FROM session WHERE last_access < ?");
        return $stmt->execute([time() - $maxlifetime]);
    }

    public function setUser(?User $user)
    {
        session_regenerate_id();

        $_SESSION[SESSION_USER_ID_KEY] = $user->id;
        $_SESSION[SESSION_USER_ROLE_KEY] = Role::USER;
    }

    public function getUser(): ?User
    {
        if (!array_key_exists(SESSION_USER_ID_KEY, $_SESSION)) {
            return null;
        }

        $userId = $_SESSION[SESSION_USER_ID_KEY];

        return $this->userRepository->findById($userId);
    }
}
