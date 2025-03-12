<?php

namespace App\Entities;

use App\Attributes\Entity\Column;
use App\Attributes\Entity\Entity;
use App\Attributes\Entity\Id;
use App\Attributes\Entity\JoinColumn;
use App\Attributes\Entity\ManyToOne;
use App\Attributes\Validation\MaxLength;
use App\Attributes\Validation\NotNull;
use DateTime;

require_once __DIR__ . '/../Attributes/Entity/Column.php';
require_once __DIR__ . '/../Attributes/Entity/Entity.php';
require_once __DIR__ . '/../Attributes/Entity/Id.php';
require_once __DIR__ . '/../Attributes/Entity/JoinColumn.php';
require_once __DIR__ . '/../Attributes/Entity/ManyToOne.php';
require_once __DIR__ . '/../Attributes/Validation/NotNull.php';
require_once __DIR__ . '/../Attributes/Validation/MaxLength.php';

#[Entity('post')]
class Post
{
	#[Id]
	#[Column('id')]
	public int $id = 0;

	#[Column('created_at')]
	public DateTime $createdAt;

	#[Column('updated_at')]
	public DateTime $updatedAt;

	#[NotNull]
	#[MaxLength(64)]
	#[Column('title')]
	public string $title;

	#[NotNull]
	#[MaxLength(512)]
	#[Column('description')]
	public string $description;

	#[ManyToOne]
	#[JoinColumn("author_id")]
	public User $author;

	public string $pictureUrl;
	public int $likeCount = 0;
	public bool $isLiked = false;
}
