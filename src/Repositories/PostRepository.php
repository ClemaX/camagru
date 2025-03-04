<?php

namespace App\Repositories;

use App\Entities\Post;
use App\EntityManager;

require_once __DIR__ . '/AbstractRepository.php';

require_once __DIR__ . '/../Entities/Post.php';

/** @extends AbstractRepository<Post> */
class PostRepository extends AbstractRepository
{
	public function __construct(EntityManager $entityManager)
	{
		parent::__construct($entityManager);
	}

	protected function getModelClass(): string
	{
		return Post::class;
	}
}
