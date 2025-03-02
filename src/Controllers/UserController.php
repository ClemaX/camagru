<?php

namespace App\Controllers;

use App\Attributes\Controller;
use App\Attributes\CurrentUser;
use App\Attributes\RequestBody;
use App\Attributes\Route;
use App\Entities\User;
use App\Exceptions\ConflictException;
use App\Exceptions\UnauthorizedException;
use App\Renderer;
use App\Services\UserService;
use ProfileUpdateDTO;

require_once __DIR__ . '/AbstractController.php';
require_once __DIR__ . '/../Services/DTOs/ProfileUpdateDTO.php';

#[Controller('/user')]
class UserController extends AbstractController
{
	public function __construct(
		Renderer $renderer,
		private readonly UserService $userService
	) {
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
			'profile' => $user->profile,
			'formUsername' => $user->username,
			'formDescription' => $user->profile->description,
			'conflict' => null,
		]);
	}

	#[Route('/self', 'PATCH')]
	public function selfUpdate(
		#[CurrentUser] ?User $user,
		#[RequestBody] ProfileUpdateDTO $dto
	) {
		if ($user === null) {
			throw new UnauthorizedException();
		}

		try {
			$user = $this->userService->updateProfile($user, $dto);
		} catch (ConflictException $e) {
			return $this->render('profile', [
				'username' => $user->username,
				'profile' => $user->profile,
				'formUsername' => $dto->username,
				'formDescription' => $dto->description,
				'conflict' => $e->getField(),
			]);
		}

		return $this->render('profile', [
			'username' => $user->username,
			'profile' => $user->profile,
			'formUsername' => $user->username,
			'formDescription' => $user->profile->description,
			'conflict' => null,
		]);
	}

	#[Route('/self/settings')]
	public function selfSettings()
	{
		return "TODO";
	}
}
