<?php

namespace App\Repositories;

use App\Entities\PostLike;
use App\EntityManager;

require_once __DIR__ . '/AbstractRepository.php';

require_once __DIR__ . '/../Entities/PostLike.php';

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
