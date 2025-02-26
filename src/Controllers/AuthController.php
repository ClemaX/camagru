<?php

namespace App\Controllers;

use App\Attributes\RequestBody;
use App\Attributes\RequestParam;
use App\Attributes\Route;
use App\Exceptions\ConflictException;
use App\Renderer;
use App\Services\AuthService;
use App\Services\DTOs\SignupDTO;
use SensitiveParameter;

require_once __DIR__ . '/../Exceptions/ConflictException.php';

class AuthController
{
    public function __construct(private AuthService $authService)
    {
    }

    #[Route('/auth/signup')]
    public function signup()
    {
        return Renderer::render("signup", [
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
            return Renderer::render("signup", [
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
        return Renderer::render("activate-account");
    }

    #[Route('/auth/activate')]
    public function activate(
        #[RequestParam] int $id,
        #[SensitiveParameter] #[RequestParam] string $token
    ) {
        $isActivated = $this->authService->activate($id, $token);

        return Renderer::render("activated-account", [
            'isActivated' => $isActivated,
        ]);
    }
}
