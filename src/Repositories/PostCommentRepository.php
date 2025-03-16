<?php

namespace App\Repositories;

use App\Entities\PostComment;
use App\EntityManager;

/** @extends AbstractRepository<PostComment> */
class PostCommentRepository extends AbstractRepository
{
	public function __construct(EntityManager $entityManager)
	{
		parent::__construct($entityManager);
	}

	protected function getModelClass(): string
	{
		return PostComment::class;
	}

	public function countByPostId(int $postId): int
	{
		return $this->countBy(['post_id' => $postId]);
	}

	/**
	 * @return PostComment[]
	 */
	public function findAllByPostId(int $postId, ?int $subjectId = null): array
	{
		return $this->findAllBy([
			'post_id' => $postId,
			'subject_id' => $subjectId
		]);
	}
}
