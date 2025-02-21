<?php

namespace App\Services;

use PDO;
use SessionHandlerInterface;

class DatabaseSessionService implements SessionHandlerInterface
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
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
}
