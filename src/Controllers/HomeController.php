<?php

namespace App\Controllers;

use App\Attributes\Controller;
use App\Attributes\CurrentUser;
use App\Attributes\Route;
use App\Entities\User;
use App\Model\Post;
use App\Renderer;

require_once __DIR__ . '/AbstractController.php';
require_once __DIR__ . '/../Models/Post.php';

#[Controller('/')]
class HomeController extends AbstractController
{
	public function __construct(Renderer $renderer)
	{
		parent::__construct($renderer);
	}

	#[Route('')]
	public function index(#[CurrentUser] ?User $user)
	{
		$username = $user !== null ? $user->username : null;

		$posts = array();

		for ($i = 0; $i < 33; $i++) {
			$posts[] = new Post(
				authorId: 0,
				authorName: 'todo',
				imageUrl: 'https://picsum.photos/id/' . 500 + $i . '/1024',
				title: 'The standard Lorem Ipsum passage, used since the 1500s',
				description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."
			);
		}

		return $this->render('home', [
			'username' => $username,
			'posts' => $posts,
		]);
	}
}
