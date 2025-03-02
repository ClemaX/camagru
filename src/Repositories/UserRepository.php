<?php

namespace App\Repositories;

use App\Entities\User;
use PDO;

require_once __DIR__ . '/AbstractRepository.php';

require_once __DIR__ . '/../Entities/User.php';

/** @extends AbstractRepository<User> */
class UserRepository extends AbstractRepository
{
    protected function getTableName(): string
    {
        return '"user"';
    }

    protected function getModelClass(): string
    {
        return User::class;
    }

    public function findByUsername(string $username): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->getTableName()} WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return null;
        }

        $modelClass = $this->getModelClass();
        return $this->load($result);
    }

    public function findByEmailAddress(string $emailAddress): ?User
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->getTableName()} WHERE email_address = :email_address");
        $stmt->execute(['email_address' => $emailAddress]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return null;
        }

        $modelClass = $this->getModelClass();
        return $this->load($result);
    }
}
