<?php

function autoload(string $class, $directory = null)
{
	if ($directory === null) {
		$directory = __DIR__;
	}

	if (str_starts_with($class, 'App\\')) {
		$filename = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 3));
		require __DIR__ . $filename . '.php';
	}
}

spl_autoload_register('autoload');
