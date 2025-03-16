<?php

namespace App\Repositories;

use App\Entities\Post;
use App\EntityManager;

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
