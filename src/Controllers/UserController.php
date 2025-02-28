<?php

namespace App\Controllers;

use App\Attributes\CurrentUser;
use App\Attributes\Route;
use App\Entities\User;
use App\Exceptions\UnauthorizedException;
use App\Renderer;

require_once __DIR__ . '/AbstractController.php';

class UserController extends AbstractController
{
    public function __construct(Renderer $renderer)
    {
        parent::__construct($renderer);
    }

    #[Route('/user/self')]
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

    #[Route('/user/self/settings')]
    public function selfSettings()
    {
        return "TODO";
    }
}
