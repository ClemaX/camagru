<?php

namespace App\Controllers;

use App\Attributes\Controller;
use App\Attributes\CurrentUser;
use App\Attributes\RequestBody;
use App\Attributes\RequestParam;
use App\Attributes\Route;
use App\Entities\User;
use App\Exceptions\ConflictException;
use App\Exceptions\UnauthorizedException;
use App\Renderer;
use App\Services\AuthService;
use App\Services\DTOs\EmailChangeDTO;
use App\Services\DTOs\PasswordChangeDTO;
use App\Services\DTOs\ProfileUpdateDTO;
use App\Services\DTOs\SettingsUpdateDTO;
use App\Services\UserService;
use SensitiveParameter;

#[Controller('/user')]
class UserController extends AbstractController
{
	public function __construct(
		Renderer $renderer,
		private readonly UserService $userService,
		private readonly AuthService $authService,
	) {
		parent::__construct($renderer);
	}

	#[Route('/self')]
	public function self(#[SensitiveParameter] #[CurrentUser] User $user): string
	{
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
		#[SensitiveParameter] #[CurrentUser] User $user,
		#[RequestBody] ProfileUpdateDTO $dto
	): string {
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
	public function selfSettings(
		#[SensitiveParameter] #[CurrentUser] User $user,
		#[RequestParam] ?string $formEmail,
		#[RequestParam] ?string $conflict
	): string {
		if ($formEmail === null) {
			$formEmail = $user->emailAddress;
		}

		return $this->render('settings', [
			'email' => $user->emailAddress,
			'settings' => $user->settings,
			'formEmail' => $formEmail,
			'conflict' => $conflict,
		]);
	}

	#[Route('/self/settings', 'PATCH')]
	public function selfSettingsUpdate(
		#[SensitiveParameter] #[CurrentUser] User $user,
		#[RequestBody] SettingsUpdateDTO $dto
	): string {
		$user = $this->userService->updateSettings($user, $dto);

		return $this->render('settings', [
			'email' => $user->emailAddress,
			'settings' => $user->settings,
			'formEmail' => $user->emailAddress,
			'conflict' => null,
		]);
	}

	#[Route('/self/new-email', 'PUT')]
	public function requestEmailChangeSubmit(
		#[SensitiveParameter] #[CurrentUser] User $user,
		#[SensitiveParameter] #[RequestBody] EmailChangeDTO $dto
	): string {
		try {
			$this->authService->requestEmailChange($user, $dto);
		} catch (ConflictException $e) {
			$conflictQueryParams = [
				'conflict' => $e->getField(),
				'formEmail' => $dto->email,
			];

			$conflictUrl = './settings'
				. '?' . http_build_query($conflictQueryParams);

			header('Location: ' . $conflictUrl);

			return '';
		}

		return $this->render('verify-new-email');
	}

	#[Route('/self/password', 'PUT')]
	public function changePasswordSubmit(
		#[SensitiveParameter] #[CurrentUser] User $user,
		#[SensitiveParameter] #[RequestBody] PasswordChangeDTO $dto
	): string {
		$this->authService->changePassword($user, $dto);

		header('Location: ./settings');

		return '';
	}
}
