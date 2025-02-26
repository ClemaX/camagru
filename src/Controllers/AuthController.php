<?php

namespace App\Controllers;

use AbstractController;
use App\Attributes\RequestBody;
use App\Attributes\RequestParam;
use App\Attributes\Route;
use App\Exceptions\ConflictException;
use App\Exceptions\UnauthorizedException;
use App\Renderer;
use App\Services\AuthService;
use App\Services\DTOs\LoginDTO;
use App\Services\DTOs\SignupDTO;
use SensitiveParameter;

require_once __DIR__ . '/AbstractController.php';

require_once __DIR__ . '/../Exceptions/ConflictException.php';

class AuthController extends AbstractController
{
    public function __construct(
        Renderer $renderer,
        private readonly AuthService $authService,
    ) {
        parent::__construct($renderer);
    }

    #[Route('/auth/signup')]
    public function signup()
    {
        return $this->render("signup", [
            "conflict" => null,
            "username" => "",
            "email" => "",
        ]);
    }

    #[Route('/auth/signup', 'POST')]
    public function signupSubmit(
        #[SensitiveParameter] #[RequestBody] SignupDTO $dto
    ) {
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
    }

    #[Route('/auth/verify-email')]
    public function verifyEmail()
    {
        return $this->render("activate-account");
    }

    #[Route('/auth/activate')]
    public function activate(
        #[RequestParam] int $id,
        #[SensitiveParameter] #[RequestParam] string $token
    ) {
        $isActivated = $this->authService->activate($id, $token);

        return $this->render("activated-account", [
            'isActivated' => $isActivated,
        ]);
    }

    #[Route('/auth/login')]
    public function login()
    {
        return $this->render("login", [
            "isInvalid" => false,
            "username" => "",
        ]);
    }

    #[Route('/auth/login', 'POST')]
    public function loginSubmit(
        #[SensitiveParameter] #[RequestBody] LoginDTO $dto
    ) {
        try {
            $this->authService->login($dto);
        } catch (UnauthorizedException) {
            return $this->render("login", [
                "isInvalid" => true,
                "username" => $dto->username
            ]);
        }

        header('Location: /');
    }
}
