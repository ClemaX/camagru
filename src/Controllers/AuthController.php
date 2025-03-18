<?php

namespace App\Controllers;

use App\Attributes\Controller;
use App\Attributes\RequestBody;
use App\Attributes\RequestParam;
use App\Attributes\Route;
use App\Exceptions\AuthException;
use App\Exceptions\ConflictException;
use App\Renderer;
use App\Response;
use App\Services\AuthService;
use App\Services\DTOs\LoginDTO;
use App\Services\DTOs\PasswordResetDTO;
use App\Services\DTOs\PasswordResetRequestDTO;
use App\Services\DTOs\SignupDTO;
use SensitiveParameter;

#[Controller('/auth')]
class AuthController extends AbstractController
{
	public function __construct(
		Renderer $renderer,
		private readonly AuthService $authService,
	) {
		parent::__construct($renderer);
	}

	#[Route('/signup')]
	public function signup(): string
	{
		return $this->render("signup", [
			"conflict" => null,
			"username" => "",
			"email" => "",
		]);
	}

	#[Route('/signup', 'POST')]
	public function signupSubmit(
		#[SensitiveParameter] #[RequestBody] SignupDTO $dto
	): Response | string {
		try {
			$this->authService->signup($dto);
		} catch (ConflictException $e) {
			return $this->render("signup", [
				"conflict" => $e->getField(),
				"username" => $dto->username,
				"email" => $dto->email,
			]);
		}

		return Response::redirect('/auth/verify-email');
	}

	#[Route('/verify-email')]
	public function verifyEmail(): string
	{
		return $this->render("activate-account");
	}

	#[Route('/activate')]
	public function activate(
		#[RequestParam] int $id,
		#[SensitiveParameter] #[RequestParam] string $token,
	): string {
		$isActivated = $this->authService->activate($id, $token);

		return $this->render("activated-account", [
			'isActivated' => $isActivated,
		]);
	}

	#[Route('/reset-password')]
	public function requestPasswordReset(): string
	{
		return $this->render('reset-password', [
			'isEmailSent' => false,
			'email' => '',
		]);
	}

	#[Route('/reset-password', 'POST')]
	public function requestPasswordResetSubmit(
		#[RequestBody] PasswordResetRequestDTO $dto
	): string {
		$this->authService->requestPasswordReset($dto);

		return $this->render('reset-password', [
			'isEmailSent' => true,
			'email' => $dto->email,
		]);
	}

	#[Route('/choose-password')]
	public function choosePassword(
		#[RequestParam] int $id,
		#[SensitiveParameter] #[RequestParam] string $token,
	): string {
		return $this->render('choose-password', [
			'isUrlInvalid' => false,
			'userId' => $id,
			'token' => $token,
		]);
	}

	#[Route('/choose-password', 'POST')]
	public function choosePasswordSubmit(
		#[RequestBody] PasswordResetDTO $dto
	): Response | string {
		$isReset = $this->authService->resetPassword($dto);

		if (!$isReset) {
			return $this->render('choose-password', [
				'isUrlInvalid' => true,
				'userId' => $dto->userId,
				'token' => $dto->token,
			]);
		}

		return Response::redirect('/');
	}

	#[Route('/change-email')]
	public function changeEmail(
		#[RequestParam] int $id,
		#[SensitiveParameter] #[RequestParam] string $token,
	): string {
		$isChanged = $this->authService->changeEmail($id, $token);

		return $this->render("changed-email", [
			'isChanged' => $isChanged,
		]);
	}

	#[Route('/login')]
	public function login(): string
	{
		return $this->render('login', [
			'errorMessage' => null,
			'username' => "",
		]);
	}

	#[Route('/login', 'POST')]
	public function loginSubmit(
		#[SensitiveParameter] #[RequestBody] LoginDTO $dto
	): Response | string {
		try {
			$this->authService->login($dto);
		} catch (AuthException $e) {
			return $this->render("login", [
				"errorMessage" => $e->getMessage(),
				"username" => $dto->username
			]);
		}

		return Response::redirect('/');
	}

	#[Route('/logout', 'POST')]
	public function logout(): Response
	{
		$this->authService->logout();

		return Response::redirect('/');
	}
}
