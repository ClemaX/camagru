<?php

namespace App\Model;

class Post
{
	public function __construct(
		public readonly string $authorId,
		public readonly string $authorName,
		public readonly string $imageUrl,
		public readonly string $title,
		public readonly string $description,
	) {
	}
}
