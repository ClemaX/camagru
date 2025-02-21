<?php

function getConfig()
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
