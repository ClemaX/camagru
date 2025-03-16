<?php

namespace App\Controllers;

use App\Attributes\Controller;
use App\Attributes\CurrentUser;
use App\Attributes\PathVariable;
use App\Attributes\RequestBody;
use App\Attributes\RequestParam;
use App\Attributes\Route;
use App\Entities\User;
use App\Exceptions\MappingException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\ValidationException;
use App\Renderer;
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
	) {
		return $this->render('gallery', [
			'posts' => $this->postService->getAll($user),
		]);
	}

	#[Route('/post')]
	public function post(
		#[SensitiveParameter] #[CurrentUser] User $user,
	) {
		return $this->render('post', []);
	}

	#[Route('/post', 'POST')]
	public function postSubmit(
		#[SensitiveParameter] #[CurrentUser] User $user,
		#[RequestBody] PostCreationDTO $dto,
	) {
		if (!array_key_exists('picture', $_FILES)
		|| $_FILES['picture']['error']
		|| empty($_FILES['picture']['tmp_name'])) {
			throw new MappingException();
		}

		$picture = $_FILES['picture'];

		header('Content-Type: application/json; charset=UTF-8');

		try {
			$post = $this->postService->post($user, $dto, $picture);
		} catch (ValidationException $e) {
			http_response_code($e->getStatusCode());
			return json_encode($e->getErrors());
		}

		http_response_code(201);
		header('Location: ./');

		return json_encode($post);
	}

	#[Route('/post/{id}/like', 'PUT')]
	public function like(
		#[SensitiveParameter] #[CurrentUser] User $user,
		#[PathVariable] string $id,
	) {
		$this->postService->like($user, (int)$id);

		header('Content-Type: application/json; charset=UTF-8');
		http_response_code(200);

		return json_encode($this->postService->countLikes((int)$id));
	}

	#[Route('/post/{id}/like', 'DELETE')]
	public function unlike(
		#[SensitiveParameter] #[CurrentUser] User $user,
		#[PathVariable] string $id,
	) {
		$this->postService->unlike($user, (int)$id);

		header('Content-Type: application/json; charset=UTF-8');
		http_response_code(200);

		return json_encode($this->postService->countLikes((int)$id));
	}

	#[Route('/post/{id}/comments', 'POST')]
	public function postComment(
		#[SensitiveParameter] #[CurrentUser] User $user,
		#[PathVariable] string $id,
		#[RequestBody] PostCommentDTO $dto,
	) {
		$comment = $this->postService->postComment($user, (int)$id, $dto);

		return json_encode($comment);
	}

	#[Route('/post/{id}/comments')]
	public function getComments(
		#[PathVariable] string $id,
		#[RequestParam] ?int $subjectId,
	) {
		$comments = $this->postService->getComments((int)$id, $subjectId);

		return json_encode($comments);
	}
}
