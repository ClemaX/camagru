<?php

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src'
	. DIRECTORY_SEPARATOR . 'autoload.php';
require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src'
	. DIRECTORY_SEPARATOR . 'config.php';

use App\Services\DatabaseSessionService;
use App\Services\AuthService;
use App\Controllers\PostController;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\EntityManager;
use App\Exceptions\HttpException;
use App\Middlewares\CsrfTokenVerificator;
use App\Middlewares\ExceptionHandler;
use App\Middlewares\FormMethodDecoder;
use App\Middlewares\JsonDecoder;
use App\Renderer;
use App\Repositories\PostCommentRepository;
use App\Repositories\PostLikeRepository;
use App\Repositories\UserRepository;
use App\Repositories\PostRepository;
use App\Router;
use App\Services\MailService;
use App\Services\PostService;
use App\Services\UserService;

mb_internal_encoding('UTF-8');

// Debug
if ($config['DEBUG'] === 'true') {
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
$postRepository = new PostRepository($entityManager);
$postLikeRepository = new PostLikeRepository($entityManager);
$postCommentRepository = new PostCommentRepository($entityManager);

// Session
$sessionService = new DatabaseSessionService(
	$pdo,
	$userRepository,
);

$sessionService->start();

$basePath = dirname($_SERVER['SCRIPT_NAME']);

// Renderer
$renderer = new Renderer(
	$sessionService,
	'Views',
	$basePath,
	$config,
);
$mailRenderer = new Renderer(
	$sessionService,
	'Views' . DIRECTORY_SEPARATOR . 'Mails',
	$config['EXTERNAL_URL'],
	$config,
);

// Middlewares
$middlewares = [
	new ExceptionHandler($renderer),
	new FormMethodDecoder($sessionService),
	new JsonDecoder($sessionService),
	new CsrfTokenVerificator($sessionService),
];

// Router
$router = new Router(
	$sessionService,
	$middlewares,
	$basePath,
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
$postService = new PostService(
	$postRepository,
	$postLikeRepository,
	$postCommentRepository,
	$config,
);

// Controllers
$router->addController(new PostController(
	$renderer,
	$postService,
));
$router->addController(new AuthController(
	$renderer,
	$authService,
));
$router->addController(new UserController(
	$renderer,
	$userService,
	$authService,
));

// Request dispatch
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
$contentType = isset($_SERVER['HTTP_CONTENT_TYPE'])
	? $_SERVER['HTTP_CONTENT_TYPE'] : null;
$body = $_POST;

$response = $router->dispatch($uri, $method, $contentType, $body);

$pdo = null;

$response->send();
