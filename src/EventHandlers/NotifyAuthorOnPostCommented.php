<?php

namespace App\EventHandlers;

use App\Events\PostCommentedEvent;
use App\Services\MailService;

class NotifyAuthorOnPostCommented
{
	public function __construct(private readonly MailService $mailService)
	{
	}

	public function handle(PostCommentedEvent $event): void
	{
		if ($event->comment->author->id === $event->post->author->id) {
			return;
		}

		$this->mailService->send(
			$event->post->author->emailAddress,
			'New comment from ' . $event->comment->author->username,
			'post-commented',
			[
				'post' => $event->post,
				'comment' => $event->comment,
			]
		);
	}
}
