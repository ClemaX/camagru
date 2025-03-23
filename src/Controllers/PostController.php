<?php

namespace App\Controllers;

use App\Attributes\Controller;
use App\Attributes\CurrentUser;
use App\Attributes\PathVariable;
use App\Attributes\RequestBody;
use App\Attributes\RequestFile;
use App\Attributes\RequestParam;
use App\Attributes\Route;
use App\Entities\PostComment;
use App\Entities\User;
use App\Enumerations\Role;
use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationException;
use App\Renderer;
use App\Response;
use App\Services\DTOs\PostCommentDTO;
use App\Services\DTOs\PostCreationDTO;
use App\Services\PostService;
use SensitiveParameter;

#[Controller('/')]
class PostController extends AbstractController
{
	public function __construct(
		Renderer $renderer,
		private readonly PostService $postService,
	) {
		parent::__construct($renderer);
	}

	#[Route('')]
	public function getAll(
		#[SensitiveParameter] #[CurrentUser] ?User $user,
	): string {
		return $this->render('gallery', [
			'posts' => $this->postService->getAll($user),
		]);
	}

	#[Route('/post')]
	public function post(
		#[SensitiveParameter] #[CurrentUser] User $user,
	): string {
		return $this->render('post', []);
	}

	#[Route('/post', 'POST')]
	public function postSubmit(
		#[SensitiveParameter] #[CurrentUser] User $user,
		#[RequestBody] PostCreationDTO $dto,
		#[RequestFile('picture')] string $pictureFilename
	): Response {
		try {
			$post = $this->postService->post($user, $dto, $pictureFilename);
		} catch (ValidationException $e) {
			return Response::json($e->getErrors(), $e->getStatusCode());
		} finally {
			unlink($pictureFilename);
		}

		return Response::json($post, 201, './');
	}

	#[Route('/{id}')]
	public function getOne(
		#[SensitiveParameter] #[CurrentUser] ?User $user,
		#[PathVariable] string $id,
	): string {
		return $this->render('gallery', [
			'posts' => [
				$this->postService->getById((int)$id, $user),
			],
		]);
	}

	#[Route('/post/{id}', 'DELETE')]
	public function postDelete(
		#[SensitiveParameter] #[CurrentUser] User $user,
		#[PathVariable] string $id,
	): Response {
		$post = $this->postService->getById((int)$id);

		if ($post->author->id !== $user->id && $user->role !== Role::ADMIN) {
			throw new ForbiddenException();
		}

		$this->postService->deletePost($post->id);

		return new Response(statusCode: 204);
	}

	/**
	 * @return mixed[]
	 */
	#[Route('/post/{id}/like', 'PUT')]
	public function like(
		#[SensitiveParameter] #[CurrentUser] User $user,
		#[PathVariable] string $id,
	): array {
		$this->postService->like($user, (int)$id);

		return [
			'likeCount' => $this->postService->countLikes((int)$id)
		];
	}

	/**
	 * @return mixed[]
	 */
	#[Route('/post/{id}/like', 'DELETE')]
	public function unlike(
		#[SensitiveParameter] #[CurrentUser] User $user,
		#[PathVariable] string $id,
	): array {
		$this->postService->unlike($user, (int)$id);

		return [
			'likeCount' => $this->postService->countLikes((int)$id)
		];
	}

	#[Route('/post/{id}/comments', 'POST')]
	public function postComment(
		#[SensitiveParameter] #[CurrentUser] User $user,
		#[PathVariable] string $id,
		#[RequestBody] PostCommentDTO $dto,
	): PostComment {
		return $this->postService->postComment($user, (int)$id, $dto);
	}

	/**
	 * @return PostComment[]
	 */
	#[Route('/post/{id}/comments')]
	public function getComments(
		#[PathVariable] string $id,
		#[RequestParam] ?int $subjectId,
	): array {
		return $this->postService->getComments((int)$id, $subjectId);
	}
}
