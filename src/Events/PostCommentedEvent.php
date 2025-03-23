<?php

namespace App\Events;

use App\Entities\Post;
use App\Entities\PostComment;

class PostCommentedEvent
{
	public function __construct(
		public readonly Post $post,
		public readonly PostComment $comment
	) {
	}
}
