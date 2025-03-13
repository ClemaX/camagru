<?php

namespace App\Controllers;

use App\Attributes\Controller;
use App\Attributes\CurrentUser;
use App\Attributes\PathVariable;
use App\Attributes\RequestBody;
use App\Attributes\Route;
use App\Entities\User;
use App\Entities\Post;
use App\Exceptions\MappingException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\ValidationException;
use App\Renderer;
use App\Services\DTOs\PostCreationDTO;
use App\Services\PostService;
use DateTime;
use SensitiveParameter;

require_once __DIR__ . '/AbstractController.php';
require_once __DIR__ . '/../Entities/Post.php';
require_once __DIR__ . '/../Services/DTOs/PostCreationDTO.php';
require_once __DIR__ . '/../Exceptions/UnauthorizedException.php';

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
	public function getAll(#[CurrentUser] ?User $user)
	{
		return $this->render('gallery', [
			'posts' => $this->postService->getAll($user),
		]);
	}

	#[Route('/post')]
	public function post(
		#[SensitiveParameter] #[CurrentUser] ?User $user,
	) {
		if ($user === null) {
			throw new UnauthorizedException();
		}

		return $this->render('post', []);
	}

	#[Route('/post', 'POST')]
	public function postSubmit(
		#[SensitiveParameter] #[CurrentUser] ?User $user,
		#[RequestBody] PostCreationDTO $dto
	) {
		if ($user === null) {
			throw new UnauthorizedException();
		}

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
	public function like(#[CurrentUser] ?User $user, #[PathVariable] string $id)
	{
		if ($user === null) {
			throw new UnauthorizedException();
		}

		$this->postService->like($user, (int)$id);

		header('Content-Type: application/json; charset=UTF-8');
		http_response_code(200);

		return json_encode($this->postService->countLikes((int)$id));
	}

	#[Route('/post/{id}/like', 'DELETE')]
	public function unlike(
		#[CurrentUser] ?User $user,
		#[PathVariable] string $id
	) {
		if ($user === null) {
			throw new UnauthorizedException();
		}

		$this->postService->unlike($user, (int)$id);

		header('Content-Type: application/json; charset=UTF-8');
		http_response_code(200);

		return json_encode($this->postService->countLikes((int)$id));
	}
}
