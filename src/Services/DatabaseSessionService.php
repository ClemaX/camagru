<?php

namespace App\Services;

use PDO;
use SessionHandlerInterface;
use App\Entities\User;
use App\Enumerations\Role;
use App\Exceptions\InternalException;
use App\Repositories\UserRepository;

define('SESSION_USER_ID_KEY', 'user_id');
define('SESSION_USER_ROLE_KEY', 'user_role');
define('SESSION_CSRF_TOKEN_KEY', 'csrf_token');
define('SESSION_CSRF_TOKEN_IAT_KEY', 'csrf_token_iat');

class DatabaseSessionService implements SessionHandlerInterface, UserSessionServiceInterface
{
	public function __construct(
		private readonly PDO $pdo,
		private readonly UserRepository $userRepository,
	) {
		session_set_save_handler($this);
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

	public function start()
	{
		if (!session_start()) {
			throw new InternalException();
		}

		if (!array_key_exists(SESSION_CSRF_TOKEN_KEY, $_SESSION)) {
			$_SESSION[SESSION_CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
		}
	}

	public function login(User $user)
	{
		session_regenerate_id();

		$_SESSION[SESSION_USER_ID_KEY] = $user->id;
		$_SESSION[SESSION_USER_ROLE_KEY] = Role::USER;
	}

	public function logout()
	{
		// Unset all of the session variables.
		/** @disregard P1003 because we want to unset the $_SESSION */
		$_SESSION = array();

		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(
				session_name(),
				'',
				time() - 42000,
				$params["path"],
				$params["domain"],
				$params["secure"],
				$params["httponly"]
			);
		}

		// Finally, destroy the session.
		session_destroy();
	}

	public function getUser(): ?User
	{
		if (!array_key_exists(SESSION_USER_ID_KEY, $_SESSION)) {
			return null;
		}

		$userId = $_SESSION[SESSION_USER_ID_KEY];

		return $this->userRepository->findById($userId);
	}

	public function getCsrfToken(): string
	{
		return $_SESSION[SESSION_CSRF_TOKEN_KEY];
	}

	public function verifyCsrfToken(string $token): bool
	{
		return hash_equals($_SESSION[SESSION_CSRF_TOKEN_KEY], $token);
	}
}
