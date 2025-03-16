<?php

/**
 * @return array<string, string>
 */
function getConfig(): array
{
	$config = require __DIR__ . '/config.default.php';

	foreach ($config as $key => $value) {
		$envValue = getenv($key);
		if ($envValue !== false) {
			$config[$key] = $envValue;
		}
	}

	return $config;
}

$config = getConfig();
