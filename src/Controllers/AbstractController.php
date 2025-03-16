<?php

namespace App\Controllers;

use App\Renderer;

abstract class AbstractController
{
	protected function __construct(private readonly Renderer $renderer)
	{
	}

	protected function render(string $templateName, array $params = []): string
	{
		$content = $this->renderer->render($templateName, $params);

		return $this->renderer->render('layout', ['content' => $content]);
	}
}
