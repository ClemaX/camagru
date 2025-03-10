<?php

namespace App\Entities;

use App\Attributes\Entity\Column;
use App\Attributes\Entity\Entity;
use App\Attributes\Entity\Id;
use App\Attributes\Validation\NotNull;
use DateTime;
use PostLikeId;

require_once __DIR__ . '/../Attributes/Entity/Column.php';
require_once __DIR__ . '/../Attributes/Entity/Entity.php';
require_once __DIR__ . '/../Attributes/Entity/Id.php';
require_once __DIR__ . '/../Attributes/Validation/NotNull.php';

#[Entity('post_like')]
class PostLike
{
	#[Id]
	public PostLikeId $id;

	#[NotNull]
	#[Column('created_at')]
	public DateTime $createdAt;
}
