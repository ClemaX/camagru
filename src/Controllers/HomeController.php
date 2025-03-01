<?php

namespace App\Controllers;

use App\Attributes\Controller;
use App\Attributes\CurrentUser;
use App\Attributes\Route;
use App\Entities\User;
use App\Renderer;

require_once __DIR__ . '/AbstractController.php';

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

        return $this->render('home', [
            'username' => $username,
        ]);
    }
}
