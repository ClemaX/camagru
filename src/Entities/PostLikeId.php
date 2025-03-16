<?php

namespace App\Entities;

use App\Attributes\Entity\Column;
use App\Attributes\Validation\NotNull;

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
