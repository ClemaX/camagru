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

#[Entity('post_comment')]
class PostComment
{
	#[Id]
	#[Column('id')]
	public int $id = 0;

	#[NotNull]
	#[Column('created_at')]
	public DateTime $createdAt;

	#[NotNull]
	#[Column('updated_at')]
	public DateTime $updatedAt;

	#[NotNull]
	#[Column('post_id')]
	public int $postId = 0;

	#[ManyToOne]
	#[JoinColumn("author_id")]
	public User $author;

	#[NotNull]
	#[Column('subject_id')]
	public int $subjectId = 0;

	#[NotNull]
	#[MaxLength(512)]
	#[Column('body')]
	public string $body;
}
