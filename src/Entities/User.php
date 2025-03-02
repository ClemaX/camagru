<?php

namespace App\Entities;

use App\Attributes\Entity\Column;
use App\Attributes\Validation\NotNull;
use App\Attributes\Validation\ValidEmailAddress;
use App\Attributes\Validation\ValidUsername;

require_once __DIR__ . '/../Attributes/Entity/Column.php';
require_once __DIR__ . '/../Attributes/Validation/NotNull.php';
require_once __DIR__ . '/../Attributes/Validation/ValidEmailAddress.php';
require_once __DIR__ . '/../Attributes/Validation/ValidUsername.php';

class User
{
    #[NotNull()]
    #[ValidEmailAddress()]
    #[Column("email_address")]
    public string $emailAddress;

    #[NotNull()]
    #[ValidUsername()]
    #[Column("username")]
    public string $username;

    #[Column("password_hash")]
    public ?string $passwordHash;

    #[Column("is_locked")]
    public bool $isLocked = true;

    #[Column("locked_at")]
    public ?int $lockedAt = null;

    #[Column("unlock_token")]
    public ?string $unlockToken = null;

    #[Column("id")]
    public int $id = 0;
}
