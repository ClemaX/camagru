<?php

namespace App\Controllers;

use App\Attributes\Controller;
use App\Attributes\CurrentUser;
use App\Attributes\RequestBody;
use App\Attributes\Route;
use App\Entities\User;
use App\Entities\Post;
use App\Exceptions\UnauthorizedException;
use App\Renderer;
use App\Services\DTOs\PostCreationDTO;
use App\Services\PostService;
use DateTime;
use SensitiveParameter;

require_once __DIR__ . '/AbstractController.php';
require_once __DIR__ . '/../Entities/Post.php';
require_once __DIR__ . '/../Entities/Post.php';
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
	public function getAll(#[SensitiveParameter] #[CurrentUser] ?User $user)
	{
		$username = $user !== null ? $user->username : null;

		$posts = array();

		$author = new User();
		$author->username = "Test author";

		for ($i = 0; $i < 33; $i++) {
			$post = new Post();
			$post->id = 500 + $i;
			$post->author = $author;
			$post->title = 'The standard Lorem Ipsum passage, used since the 1500s';
			$post->description = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";
			$post->createdAt = new DateTime();
			$post->updatedAt = new DateTime();

			$posts[] = $post;
		}

		return $this->render('gallery', [
			'username' => $username,
			'posts' => $posts,
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

		$this->postService->post($user, $dto);
	}
}
