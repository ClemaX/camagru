<?php

namespace App\Repositories;

use App\Entities\PostLike;
use App\EntityManager;

/** @extends AbstractRepository<PostLike> */
class PostLikeRepository extends AbstractRepository
{
	public function __construct(EntityManager $entityManager)
	{
		parent::__construct($entityManager);
	}

	protected function getModelClass(): string
	{
		return PostLike::class;
	}

	public function countByPostId(int $postId)
	{
		return $this->countBy(['post_id' => $postId]);
	}
}
