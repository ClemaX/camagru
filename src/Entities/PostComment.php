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

#[Entity('post_comment')]
class PostComment extends AbstractJsonSerializableEntity
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

	#[Column('subject_id')]
	public ?int $subjectId;

	#[NotNull]
	#[MaxLength(512)]
	#[Column('body')]
	public string $body;
}
