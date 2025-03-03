<?php

use App\Router;
use App\Repositories\UserRepository;
use App\Services\DatabaseSessionService;
use App\Services\AuthService;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\EntityManager;
use App\Exceptions\HttpException;
use App\Renderer;
use App\Services\MailService;
use App\Services\UserService;

require __DIR__ . '/../src/config.php';
require __DIR__ . '/../src/Router.php';
require __DIR__ . '/../src/Renderer.php';
require __DIR__ . '/../src/Repositories/UserRepository.php';
require __DIR__ . '/../src/Services/DatabaseSessionService.php';
require __DIR__ . '/../src/Services/AuthService.php';
require __DIR__ . '/../src/Services/UserService.php';
require __DIR__ . '/../src/Controllers/HomeController.php';
require __DIR__ . '/../src/Controllers/AuthController.php';
require __DIR__ . '/../src/Controllers/UserController.php';
require __DIR__ . '/../src/EntityManager.php';

// Debug
if (strcmp($config['DEBUG'], 'true') === 0) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

// Database
try {
	$pdo = new PDO(
		$config['DATABASE_DSN'],
		$config['DATABASE_USERNAME'],
		$config['DATABASE_PASSWORD']
	);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	die('Connection failed: ' . $e->getMessage());
}

// Entity Manager
$entityManager = new EntityManager($pdo);

// Repositories
$userRepository = new UserRepository($entityManager);

// Session
$sessionService = new DatabaseSessionService(
	$pdo,
	$userRepository,
);

$sessionService->start();

// Router
$router = new Router(
	$sessionService,
);

// Renderer
$renderer = new Renderer(
	$sessionService,
	'Views',
	$router->basePath,
	$config,
);
$mailRenderer = new Renderer(
	$sessionService,
	'Views' . DIRECTORY_SEPARATOR . 'Mails',
	$config['EXTERNAL_URL'],
	$config,
);

// Services
$mailService = new MailService(
	$mailRenderer,
);
$authService = new AuthService(
	$userRepository,
	$sessionService,
	$mailService,
	$config,
);
$userService = new UserService(
	$userRepository,
);

// Controllers
$router->addController(new HomeController($renderer));
$router->addController(new AuthController($renderer, $authService));
$router->addController(new UserController($renderer, $userService, $authService));

// Request dispatch
try {
	$content = $router->dispatch(
		$_SERVER['REQUEST_URI'],
		$_SERVER['REQUEST_METHOD'],
	);
} catch (HttpException $e) {
	$content = $renderer->render('error', [
		'title' => $e->getTitle(),
		'message' => $e->getMessage(),
		'code' => $e->getCode(),
	]);

	$e->sendHeaders();
}

$pdo = null;

echo $renderer->render('layout', [
	"content" => $content,
]);
