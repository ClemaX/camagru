<?php

namespace App\Controllers;

use AbstractController;
use App\Attributes\CurrentUser;
use App\Attributes\Route as AttributesRoute;
use App\Entities\User;
use App\Renderer;

require_once __DIR__ . '/AbstractController.php';

class HomeController extends AbstractController
{
    public function __construct(Renderer $renderer)
    {
        parent::__construct($renderer);
    }

    #[AttributesRoute('/')]
    public function index(#[CurrentUser] ?User $user)
    {
        $username = $user !== null ? $user->username : null;

        return $this->render('home', [
            'username' => $username,
        ]);
    }
}
