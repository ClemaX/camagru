<?php

namespace App\Controllers;

use AbstractController;
use App\Renderer;

require_once __DIR__ . '/AbstractController.php';

class UserController extends AbstractController
{
    public function __construct(Renderer $renderer)
    {
        parent::__construct($renderer);
    }

    public function self()
    {
        return "TODO";
    }
}
