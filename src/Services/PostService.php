<?php

namespace App\Services;

use App\Entities\Post;
use App\Entities\User;
use App\Repositories\PostRepository;
use App\Services\DTOs\PostCreationDTO;
use DateTime;

require_once __DIR__ . '/../Entities/Post.php';
require_once __DIR__ . '/../Repositories/PostRepository.php';

class PostService
{
	public function __construct(private PostRepository $postRepository)
	{
	}

	/** @return Post[] */
	public function getAll(): array
	{
		return $this->postRepository->findAll();
	}

	public function post(User $author, PostCreationDTO $dto): Post
	{
		$post = new Post();

		$post->author = $author;
		$post->title = $dto->title;
		$post->description = $dto->description;

		$post->createdAt = new DateTime();
		$post->updatedAt = new DateTime();

		return $this->postRepository->save($post);
	}

	// public function update(PostUpdateDTO $dto): User
	// {
	// 	throw new InternalException("Not implemented yet");
	// }
}
