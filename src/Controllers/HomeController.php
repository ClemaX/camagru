<?php

namespace App\Controllers;

use App\Attributes\CurrentUser;
use App\Attributes\Route as AttributesRoute;
use App\Entities\User;
use App\Renderer;

class HomeController
{
    #[AttributesRoute('/')]
    public function index(#[CurrentUser] ?User $user)
    {
		$username = $user !== null ? $user->username : null;

		return Renderer::render('home', [
			'username' => $username,
		]);
    }
}
