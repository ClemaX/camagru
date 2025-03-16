<?php

namespace App\Services;

use App\Entities\Post;
use App\Entities\PostComment;
use App\Entities\PostLike;
use App\Entities\PostLikeId;
use App\Entities\User;
use App\Exceptions\ConflictException;
use App\Exceptions\InternalException;
use App\Exceptions\NotFoundException;
use App\Repositories\PostCommentRepository;
use App\Repositories\PostLikeRepository;
use App\Repositories\PostRepository;
use App\Services\DTOs\PostCommentDTO;
use App\Services\DTOs\PostCreationDTO;
use App\SvgSanitizer;
use DateTime;
use Exception;
use SensitiveParameter;

class PostService
{
	private readonly SvgSanitizer $svgSanitizer;
	private readonly string $storageDirectory;
	private readonly string $externalStorageUrl;
	private readonly string $bucketId;

	public function __construct(
		private readonly PostRepository $postRepository,
		private readonly PostLikeRepository $likeRepository,
		private readonly PostCommentRepository $commentRepository,
		array $config,
	) {
		$this->svgSanitizer = new SvgSanitizer([
			'data:image/png;base64,',
			$config['EXTERNAL_URL'],
			$config['STORAGE_EXTERNAL_URL'],
		]);

		$this->externalStorageUrl = $config['STORAGE_EXTERNAL_URL'];
		$this->bucketId = $config['POST_PICTURE_BUCKET_ID'];

		$this->storageDirectory = $config['STORAGE_DIRECTORY'] . '/' . $this->bucketId;
	}

	/** @return Post[] */
	public function getAll(?User $viewer): array
	{
		return array_map(function ($post) use ($viewer) {
			$post->pictureUrl = $this->externalStorageUrl . '/'
				. $this->bucketId . '/'
				. $post->id . '/';
			$post->likeCount = $this->countLikes($post->id);
			$post->commentCount = $this->countComments($post->id);

			if ($viewer !== null) {
				$viewerLikeId = new PostLikeId(
					authorId: $viewer->id,
					postId: $post->id
				);

				$post->isLiked = $this->likeRepository->existsById($viewerLikeId);
			}

			return $post;
		}, $this->postRepository->findAll('created_at', 'DESC'));
	}

	public function post(
		#[SensitiveParameter] User $author,
		PostCreationDTO $dto,
		array $pictureFile
	): Post {
		$temporaryDirectory = tempnam(sys_get_temp_dir(), '');
		if ($temporaryDirectory === false || !unlink($temporaryDirectory)
		|| !mkdir($temporaryDirectory, permissions: 0700)) {
			throw new InternalException('Could not create temporary directory');
		}

		try {
			$this->svgSanitizer->sanitize(
				$pictureFile['tmp_name'],
				$temporaryDirectory
			);
		} catch (Exception $e) {
			rmdir($temporaryDirectory);
			throw $e;
		}

		$now = new DateTime();

		$post = new Post();
		$post->author = $author;
		$post->title = $dto->title;
		$post->description = $dto->description;
		$post->createdAt = $now;
		$post->updatedAt = $now;

		$post = $this->postRepository->save($post);

		// TODO: Use transactions to commit instead of reverting save

		$postDirectory = $this->storageDirectory . '/' . $post->id;

		if ((file_exists($postDirectory)
		|| !mkdir($postDirectory, permissions: 0755, recursive: true))) {
			$this->postRepository->delete($post->id);
			rmdir($temporaryDirectory);
			throw new InternalException('Could not create directory');
		}

		$success = !!($temporaryDirectoryHandle = opendir($temporaryDirectory));

		while ($success && $file = readdir($temporaryDirectoryHandle)) {
			if (!str_starts_with($file, '.')) {
				$success = rename(
					$temporaryDirectory . '/' . $file,
					$postDirectory . '/' . $file
				);
			}
		}

		rmdir($temporaryDirectory);

		if (!$success) {
			$this->postRepository->delete($post->id);
			rmdir($postDirectory);
			throw new InternalException('Could not create directory');
		}

		return $post;
	}

	// public function update(PostUpdateDTO $dto): User
	// {
	// 	throw new InternalException("Not implemented yet");
	// }

	public function like(
		#[SensitiveParameter] User $author,
		int $postId
	) {
		if (!$this->postRepository->existsById($postId)) {
			throw new NotFoundException();
		}

		$likeId = new PostLikeId(authorId: $author->id, postId: $postId);

		if ($this->likeRepository->existsById($likeId)) {
			throw new ConflictException('id');
		}

		$like = new PostLike();
		$like->id = $likeId;
		$like->createdAt = new DateTime();

		$this->likeRepository->save($like);
	}

	public function unlike(
		#[SensitiveParameter] User $author,
		int $postId,
	) {
		$likeId = new PostLikeId(authorId: $author->id, postId: $postId);

		if ($this->likeRepository->delete($likeId) !== 1) {
			throw new NotFoundException();
		}
	}

	public function countLikes(int $postId)
	{
		return $this->likeRepository->countByPostId($postId);
	}

	public function postComment(
		#[SensitiveParameter] User $author,
		int $postId,
		PostCommentDTO $dto,
	): PostComment {
		$now = new DateTime();

		$comment = new PostComment();

		$comment->author = $author;
		$comment->postId = $postId;
		$comment->subjectId = $dto->subjectId;
		$comment->body = $dto->body;
		$comment->createdAt = $now;
		$comment->updatedAt = $now;

		return $this->commentRepository->save($comment);
	}

	public function countComments(int $postId)
	{
		return $this->commentRepository->countByPostId($postId);
	}

	public function getComments(int $postId, ?int $subjectId = null)
	{
		return $this->commentRepository->findAllByPostId($postId, $subjectId);
	}
}
