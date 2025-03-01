<?php

namespace App\Controllers;

use App\Attributes\Controller;
use App\Attributes\RequestBody;
use App\Attributes\RequestParam;
use App\Attributes\Route;
use App\Exceptions\ConflictException;
use App\Renderer;
use App\Services\AuthService;
use App\Services\DTOs\LoginDTO;
use App\Services\DTOs\PasswordResetDTO;
use App\Services\DTOs\PasswordResetRequestDTO;
use App\Services\DTOs\SignupDTO;
use AuthException;
use SensitiveParameter;

require_once __DIR__ . '/AbstractController.php';

require_once __DIR__ . '/../Exceptions/ConflictException.php';

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
    ): string {
        try {
            $this->authService->signup($dto);
        } catch (ConflictException $e) {
            return $this->render("signup", [
                "conflict" => $e->getField(),
                "username" => $dto->username,
                "email" => $dto->email,
            ]);
        }

        header('Location: /auth/verify-email');

        return '';
    }

    #[Route('/verify-email')]
    public function verifyEmail()
    {
        return $this->render("activate-account");
    }

    #[Route('/activate')]
    public function activate(
        #[RequestParam] int $id,
        #[SensitiveParameter] #[RequestParam] string $token,
    ) {
        $isActivated = $this->authService->activate($id, $token);

        return $this->render("activated-account", [
            'isActivated' => $isActivated,
        ]);
    }

    #[Route('/reset-password')]
    public function requestPasswordReset()
    {
        return $this->render('reset-password', [
            'isEmailSent' => false,
            'email' => '',
        ]);
    }

    #[Route('/reset-password', 'POST')]
    public function requestPasswordResetSubmit(
        #[RequestBody] PasswordResetRequestDTO $dto
    ) {
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
    ) {
        return $this->render('choose-password', [
            'isUrlInvalid' => false,
            'userId' => $id,
            'token' => $token,
        ]);
    }

    #[Route('/choose-password', 'POST')]
    public function choosePasswordSubmit(
        #[RequestBody] PasswordResetDTO $dto
    ) {
        $isReset = $this->authService->resetPassword($dto);

        if (!$isReset) {
            return $this->render('choose-password', [
                'isUrlInvalid' => true,
                'userId' => $dto->userId,
                'token' => $dto->token,
            ]);
        }

        header('Location: /');

        return '';
    }

    #[Route('/login')]
    public function login()
    {
        return $this->render('login', [
            'errorMessage' => null,
            'username' => "",
        ]);
    }

    #[Route('/login', 'POST')]
    public function loginSubmit(
        #[SensitiveParameter] #[RequestBody] LoginDTO $dto
    ): string {
        try {
            $this->authService->login($dto);
        } catch (AuthException $e) {
            return $this->render("login", [
                "errorMessage" => $e->getMessage(),
                "username" => $dto->username
            ]);
        }

        header('Location: /');

        return '';
    }

    #[Route('/logout', 'POST')]
    public function logout(): string
    {
        $this->authService->logout();

        header('Location: /');

        return '';
    }
}
