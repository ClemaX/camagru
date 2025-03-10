<?php

namespace App\Repositories;

use App\Entities\PostComment;
use App\EntityManager;

require_once __DIR__ . '/AbstractRepository.php';

require_once __DIR__ . '/../Entities/PostComment.php';

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
}
