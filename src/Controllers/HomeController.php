<?php

namespace App\Controllers;

use App\Attributes\Controller;
use App\Attributes\CurrentUser;
use App\Attributes\Route;
use App\Entities\User;
use App\Model\Image;
use App\Renderer;

require_once __DIR__ . '/AbstractController.php';
require_once __DIR__ . '/../Models/Image.php';

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

        $images = array();

        for ($i = 0; $i < 33; $i++) {
            $images[] = new Image('https://picsum.photos/id/' . 500 + $i . '/512', 'Image ' . $i, 'Hello world');
        }

        return $this->render('home', [
            'username' => $username,
            'images' => $images,
        ]);
    }
}
