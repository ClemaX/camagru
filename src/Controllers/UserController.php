<?php

namespace App\Controllers;

use App\Attributes\Controller;
use App\Attributes\CurrentUser;
use App\Attributes\Route;
use App\Entities\User;
use App\Exceptions\UnauthorizedException;
use App\Renderer;

require_once __DIR__ . '/AbstractController.php';

#[Controller('/user')]
class UserController extends AbstractController
{
	public function __construct(Renderer $renderer)
	{
		parent::__construct($renderer);
	}

	#[Route('/self')]
	public function self(#[CurrentUser] ?User $user)
	{
		if ($user === null) {
			throw new UnauthorizedException();
		}

		return $this->render('profile', [
			'username' => $user->username,
			'email' => $user->emailAddress,
		]);
	}

	#[Route('/self/settings')]
	public function selfSettings()
	{
		return "TODO";
	}
}
