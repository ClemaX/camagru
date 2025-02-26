<?php

use App\Renderer;

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
