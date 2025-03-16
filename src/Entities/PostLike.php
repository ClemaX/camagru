<?php

namespace App\Entities;

use App\Attributes\Entity\Column;
use App\Attributes\Entity\Entity;
use App\Attributes\Entity\Id;
use App\Attributes\Validation\NotNull;
use DateTime;

#[Entity('post_like')]
class PostLike
{
	#[Id]
	public PostLikeId $id;

	#[NotNull]
	#[Column('created_at')]
	public DateTime $createdAt;
}
