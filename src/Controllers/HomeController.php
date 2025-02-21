<?php

namespace App\Controllers;

use App\Attributes\Route as AttributesRoute;
use App\Renderer;

class HomeController
{
    #[AttributesRoute('/')]
    public function index()
    {
        return Renderer::render('home');
    }
}
