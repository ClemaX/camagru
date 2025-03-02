<?php

namespace App;

use App\Attributes\Controller;
use App\Attributes\CurrentUser;
use App\Attributes\Route;
use App\Attributes\RequestBody;
use App\Attributes\RequestParam;
use App\Exceptions\InvalidCsrfTokenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Services\UserSessionServiceInterface;
use ReflectionClass;
use ReflectionMethod;
use SensitiveParameter;

require_once __DIR__ . '/Attributes/Controller.php';
require_once __DIR__ . '/Attributes/CurrentUser.php';
require_once __DIR__ . '/Attributes/RequestBody.php';
require_once __DIR__ . '/Attributes/RequestParam.php';
require_once __DIR__ . '/Attributes/Route.php';
require_once __DIR__ . '/Exceptions/InvalidCsrfTokenException.php';
require_once __DIR__ . '/Exceptions/NotFoundException.php';
require_once __DIR__ . '/Services/UserSessionServiceInterface.php';
require_once __DIR__ . '/Mapper.php';

class Router
{
	private array $routes = [];
	public readonly string $basePath;

	public function __construct(
		private UserSessionServiceInterface $sessionService,
		private Mapper $mapper = new Mapper(),
		?string $basePath = null,
	) {
		$this->basePath = $basePath !== null
			? $basePath
			: dirname($_SERVER['SCRIPT_NAME']);
	}

	private function getMethodParameters(ReflectionMethod $method): array
	{
		$parameters = [];
		foreach ($method->getParameters() as $param) {
			$attrs = $param->getAttributes(RequestBody::class);
			if (!empty($attrs)) {
				$attribute = $attrs[0]->newInstance();

				$parameters[] = [
					'name' => $param->getName(),
					'type' => $param->getType()->getName(),
					'kind' => $attribute::class,
				];
			}

			$attrs = $param->getAttributes(RequestParam::class);
			if (!empty($attrs)) {
				$attribute = $attrs[0]->newInstance();

				$parameters[] = [
					'name' => $attribute->getName()
						?? $param->getName(),
					'type' => $param->getType()->getName(),
					'kind' => $attribute::class,
				];
			}

			$attrs = $param->getAttributes(CurrentUser::class);
			if (!empty($attrs)) {
				$attribute = $attrs[0]->newInstance();

				$parameters[] = [
					'name' => $param->getName(),
					'type' => $param->getType()->getName(),
					'kind' => $attribute::class,
				];
			}
		}
		return $parameters;
	}

	private function prepareArguments(
		array $parameters,
		array $requestParams
	): array {
		$args = [];
		foreach ($parameters as $param) {
			$dto = null;

			if (strcmp($param['kind'], RequestBody::class) === 0) {
				if (!array_key_exists('_token', $_POST)
				|| !$this->sessionService->verifyCsrfToken($_POST['_token'])) {
					throw new InvalidCsrfTokenException();
				}
				$dto = $this->mapper->map($param['type'], $_POST);
			} elseif (strcmp($param['kind'], RequestParam::class) === 0) {
				if (array_key_exists($param['name'], $requestParams)) {
					$dto = $requestParams[$param['name']];
				}
			} elseif (strcmp($param['kind'], CurrentUser::class) === 0) {
				if (array_key_exists('user_id', $_SESSION)) {
					$dto = $this->sessionService->getUser();
				}
			}

			$args[] = $dto;
		}

		return $args;
	}

	// public function addController(string $controllerClass)
	// {
	//     $reflectionClass = new ReflectionClass($controllerClass);
	//     $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

	//     foreach ($methods as $method) {
	//         $attributes = $method->getAttributes(Route::class);
	//         foreach ($attributes as $attribute) {
	//             $route = $attribute->newInstance();
	//             $this->routes[] = [
	//                 'path' => $route->path,
	//                 'method' => $route->method,
	//                 'controller' => $controllerClass,
	//                 'action' => $method->getName(),
	//                 'parameters' => $this->getMethodParameters($method),
	//             ];
	//         }
	//     }
	// }

	public function addController(object $controller)
	{
		$reflectionClass = new ReflectionClass($controller::class);

		$controllerAttributes = $reflectionClass->getAttributes(Controller::class);
		$controllerAttribute = reset($controllerAttributes);
		$controllerPath = $controllerAttribute !== false ? $controllerAttribute->newInstance()->path : '/';

		$methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

		foreach ($methods as $method) {
			$attributes = $method->getAttributes(Route::class);
			foreach ($attributes as $attribute) {
				$route = $attribute->newInstance();
				$path = rtrim($controllerPath, '/') . '/' . ltrim($route->path, '/');
				$this->routes[] = [
					'path' => $path,
					'method' => $route->method,
					'controller' => $controller,
					'action' => $method->getName(),
					'parameters' => $this->getMethodParameters($method),
				];
			}
		}
	}

	// public function scanControllersInDirectory(string $directory)
	// {
	//     $files = scandir($directory);

	//     foreach ($files as $file) {
	//         if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
	//             require_once $directory . '/' . $file;
	//             $className = 'App\\Controllers\\' . pathinfo($file, PATHINFO_FILENAME);

	//             if (class_exists($className)) {
	//                 $this->addController($className);
	//             }
	//         }
	//     }
	// }

	public function dispatch(
		#[SensitiveParameter] string $requestUri,
		string $requestMethod
	): string {
		$urlParts = parse_url($requestUri);

		$requestPath = $urlParts['path'];

		if (array_key_exists('query', $urlParts)) {
			parse_str($urlParts['query'], $requestParams);
		} else {
			$requestParams = [];
		}

		foreach ($this->routes as $route) {
			if ($route['path'] === $requestPath
			&& $route['method'] === $requestMethod) {
				$controller = $route['controller'];
				$args = $this->prepareArguments(
					$route['parameters'],
					$requestParams
				);
				return $controller->{$route['action']}(...$args);
			}
		}

		throw new NotFoundException();
	}
}
