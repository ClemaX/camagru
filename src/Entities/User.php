<?php

namespace App\Entities;

use App\Attributes\Entity\Column;
use App\Attributes\Entity\Entity;
use App\Attributes\Entity\Id;
use App\Attributes\Entity\OneToOne;
use App\Attributes\Entity\PrimaryKeyJoinColumn;
use App\Attributes\Serialization\JsonIgnore;
use App\Attributes\Validation\NotNull;
use App\Attributes\Validation\ValidEmailAddress;
use App\Attributes\Validation\ValidUsername;
use App\Enumerations\Role;

#[Entity('"user"')]
class User extends AbstractJsonSerializableEntity
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

	#[JsonIgnore]
	#[Column('password_hash')]
	public ?string $passwordHash;

	#[JsonIgnore]
	#[Column('is_locked')]
	public bool $isLocked = true;

	#[JsonIgnore]
	#[Column('locked_at')]
	public ?int $lockedAt = null;

	#[JsonIgnore]
	#[Column('unlock_token')]
	public ?string $unlockToken = null;

	#[Column('role_id')]
	public Role $role;

	#[OneToOne]
	#[PrimaryKeyJoinColumn]
	public UserProfile $profile;

	#[JsonIgnore]
	#[OneToOne]
	#[PrimaryKeyJoinColumn]
	public UserSettings $settings;

	#[JsonIgnore]
	#[Column('email_change_address')]
	public ?string $emailChangeAddress = null;

	#[JsonIgnore]
	#[Column('email_change_requested_at')]
	public ?int $emailChangeRequestedAt = null;

	#[JsonIgnore]
	#[Column('email_change_token')]
	public ?string $emailChangeToken = null;
}
