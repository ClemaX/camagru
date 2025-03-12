<?php

namespace App;

use App\Attributes\Controller;
use App\Attributes\CurrentUser;
use App\Attributes\PathVariable;
use App\Attributes\Route;
use App\Attributes\RequestBody;
use App\Attributes\RequestParam;
use App\Exceptions\InvalidCsrfTokenException;
use App\Exceptions\NotFoundException;
use App\Services\UserSessionServiceInterface;
use ReflectionClass;
use ReflectionMethod;
use SensitiveParameter;

require_once __DIR__ . '/Attributes/Controller.php';
require_once __DIR__ . '/Attributes/CurrentUser.php';
require_once __DIR__ . '/Attributes/PathVariable.php';
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
			$attrs = $param->getAttributes(PathVariable::class);
			if (!empty($attrs)) {
				$attribute = $attrs[0]->newInstance();

				$parameters[] = [
					'name' => $attribute->getName()
						?? $param->getName(),
					'type' => $param->getType()->getName(),
					'kind' => $attribute::class,
				];
			}

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
		array $pathVariables,
		array $requestParams
	): array {
		$args = [];
		foreach ($parameters as $param) {
			$dto = null;

			// FIXME: Replace strcmp with ===

			if (strcmp($param['kind'], PathVariable::class) === 0) {
				$dto = $pathVariables[$param['name']];
			} elseif (strcmp($param['kind'], RequestBody::class) === 0) {
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

	public static function capturePathVariables(
		string $routePattern,
		string $requestPath
	) {
		$regexPattern = preg_replace('/\{(\w+)\}/', '(?P<\1>[^/]+)', $routePattern);
		$regexPattern = '#^' . $regexPattern . '$#';

		if (!preg_match($regexPattern, $requestPath, $matches)) {
			$matches = [];
		}

		return $matches;
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
		$controllerPath = $controllerAttribute !== false ?
			$controllerAttribute->newInstance()->path : '/';

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

		if ($requestMethod === 'POST' && array_key_exists('_method', $_POST)) {
			$requestMethod = $_POST['_method'];
		}

		foreach ($this->routes as $route) {
			if ($route['method'] === $requestMethod) {
				$pathVariables = self::capturePathVariables(
					$route['path'],
					$requestPath
				);

				if (!empty($pathVariables)) {
					$controller = $route['controller'];

					$args = $this->prepareArguments(
						$route['parameters'],
						$pathVariables,
						$requestParams
					);

					return $controller->{$route['action']}(...$args);
				}
			}
		}

		throw new NotFoundException();
	}
}
