<?php

namespace App\Entities;

use App\Attributes\Validation\NotNull;
use App\Attributes\Validation\ValidEmailAddress;
use App\Attributes\Validation\ValidUsername;

require_once __DIR__ . '/../Attributes/Validation/NotNull.php';
require_once __DIR__ . '/../Attributes/Validation/ValidEmailAddress.php';
require_once __DIR__ . '/../Attributes/Validation/ValidUsername.php';

class User
{
    public function __construct(
        #[NotNull()]
        #[ValidEmailAddress()]
        public string $emailAddress,
        #[NotNull()]
        #[ValidUsername()]
        public string $username,
        public ?string $passwordHash,
        public bool $isLocked = true,
        public ?int $lockedAt = null,
        public ?string $unlockToken = null,
        public int $id = 0,
    ) {
    }

    public static function load(array $data): User
    {
        return new User(
            emailAddress: $data["email_address"],
            username: $data["username"],
            passwordHash: $data["password_hash"],
            isLocked: $data["is_locked"],
            lockedAt: $data["locked_at"],
            unlockToken: $data["unlock_token"],
            id: $data["id"],
        );
    }

    public function toArray(): array
    {
        return [
            "email_address" => $this->emailAddress,
            "username" => $this->username,
            "password_hash" => $this->passwordHash,
            "is_locked" => $this->isLocked,
            "locked_at" => $this->lockedAt,
            "unlock_token" => $this->unlockToken,
            "id" => $this->id,
        ];
    }
}
