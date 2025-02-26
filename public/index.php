<?php

use App\Router;
use App\Repositories\UserRepository;
use App\Services\DatabaseSessionService;
use App\Services\AuthService;
use App\Controllers\HomeController;
use App\Controllers\AuthController;

require __DIR__ . '/../src/config.php';
require __DIR__ . '/../src/Validator.php';
require __DIR__ . '/../src/Router.php';
require_once __DIR__ . '/../src/Renderer.php';
require __DIR__ . '/../src/Attributes/Route.php';
require __DIR__ . '/../src/Repositories/UserRepository.php';
require __DIR__ . '/../src/Services/DatabaseSessionService.php';
require __DIR__ . '/../src/Services/AuthService.php';
require __DIR__ . '/../src/Controllers/HomeController.php';
require __DIR__ . '/../src/Controllers/AuthController.php';

// Debug
if (strcmp($config['DEBUG'], 'true') === 0) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Database
try {
    $pdo = new PDO($config['DATABASE_DSN'], $config['DATABASE_USERNAME'], $config['DATABASE_PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

// Session
$sessionService = new DatabaseSessionService($pdo);
session_set_save_handler($sessionService, true);

session_start();

// Repositories
$userRepository = new UserRepository($pdo);

// Services
$authService = new AuthService($userRepository, $config);
$validator = new Validator();

// Router
$router = new Router($authService, $validator);

$router->addController(new HomeController());
$router->addController(new AuthController($authService));

// Request dispatch
$content = $router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

$pdo = null;

require __DIR__ . '/../src/Views/layout.php';
