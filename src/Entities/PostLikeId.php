<?php

use App\Attributes\Entity\Column;
use App\Attributes\Validation\NotNull;

require_once __DIR__ . '/../Attributes/Entity/Column.php';

class PostLikeId
{
	public function __construct(
		#[NotNull]
		#[Column('author_id')]
		public int $authorId,
		#[NotNull]
		#[Column('post_id')]
		public int $postId,
	) {
	}
}
