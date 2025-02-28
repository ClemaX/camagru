<?php

namespace App\Controllers;

use App\Renderer;

require_once __DIR__ . '/../Attributes/Route.php';

abstract class AbstractController
{
    protected function __construct(private readonly Renderer $renderer)
    {
    }

    protected function render(string $templateName, array $params = []): string
    {
        return $this->renderer->render($templateName, $params);
    }
}
