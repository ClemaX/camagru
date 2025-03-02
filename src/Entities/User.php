<?php

namespace App\Entities;

use App\Attributes\Entity\Column;
use App\Attributes\Entity\Entity;
use App\Attributes\Entity\Id;
// use App\Attributes\Entity\JoinTable;
// use App\Attributes\Entity\ManyToMany;
use App\Attributes\Entity\OneToOne;
use App\Attributes\Entity\PrimaryKeyJoinColumn;
use App\Attributes\Validation\NotNull;
use App\Attributes\Validation\ValidEmailAddress;
use App\Attributes\Validation\ValidUsername;

require_once __DIR__ . '/../Attributes/Entity/Column.php';
require_once __DIR__ . '/../Attributes/Entity/Entity.php';
require_once __DIR__ . '/../Attributes/Entity/Id.php';
// require_once __DIR__ . '/../Attributes/Entity/JoinTable.php';
// require_once __DIR__ . '/../Attributes/Entity/ManyToMany.php';
require_once __DIR__ . '/../Attributes/Entity/OneToOne.php';
require_once __DIR__ . '/../Attributes/Validation/NotNull.php';
require_once __DIR__ . '/../Attributes/Validation/ValidEmailAddress.php';
require_once __DIR__ . '/../Attributes/Validation/ValidUsername.php';
require_once __DIR__ . '/UserProfile.php';
require_once __DIR__ . '/UserRole.php';

#[Entity('"user"')]
class User
{
	#[Id]
	#[Column('id')]
	public int $id = 0;

	#[NotNull]
	#[ValidEmailAddress]
	#[Column('email_address')]
	public string $emailAddress;

	#[NotNull]
	#[ValidUsername]
	#[Column('username')]
	public string $username;

	#[Column('password_hash')]
	public ?string $passwordHash;

	#[Column('is_locked')]
	public bool $isLocked = true;

	#[Column('locked_at')]
	public ?int $lockedAt = null;

	#[Column('unlock_token')]
	public ?string $unlockToken = null;

	#[OneToOne]
	#[PrimaryKeyJoinColumn]
	public UserProfile $profile;

	// #[ManyToMany]
	// #[JoinTable('user_role')]
	// public UserRole $role;
}
