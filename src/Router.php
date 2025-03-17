<?php

namespace App;

use App\Attributes\Controller;
use App\Attributes\CurrentUser;
use App\Attributes\PathVariable;
use App\Attributes\Route;
use App\Attributes\RequestBody;
use App\Attributes\RequestFile;
use App\Attributes\RequestParam;
use App\Exceptions\InvalidCsrfTokenException;
use App\Exceptions\MappingException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Services\UserSessionServiceInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use SensitiveParameter;

class Router
{
	/** @var array<string, mixed> $routes */
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

	/**
	 * @return array<string, class-string | string | bool>[]
	 */
	private function getMethodParameters(ReflectionMethod $method): array
	{
		$parameters = [];
		foreach ($method->getParameters() as $param) {
			$type = $param->getType();
			assert($type instanceof ReflectionNamedType);

			$attrs = $param->getAttributes(PathVariable::class);

			if (!empty($attrs)) {
				$attribute = $attrs[0]->newInstance();

				$parameters[] = [
					'name' => $attribute->getName()
						?? $param->getName(),
					'type' => $type->getName(),
					'kind' => $attribute::class,
				];
			}

			$attrs = $param->getAttributes(RequestBody::class);
			if (!empty($attrs)) {
				$attribute = $attrs[0]->newInstance();

				$parameters[] = [
					'name' => $param->getName(),
					'type' => $type->getName(),
					'kind' => $attribute::class,
				];
			}

			$attrs = $param->getAttributes(RequestParam::class);
			if (!empty($attrs)) {
				$attribute = $attrs[0]->newInstance();

				$parameters[] = [
					'name' => $attribute->getName()
						?? $param->getName(),
					'type' => $type->getName(),
					'required' => !$type->allowsNull(),
					'kind' => $attribute::class,
				];
			}

			$attrs = $param->getAttributes(RequestFile::class);
			if (!empty($attrs)) {
				$attribute = $attrs[0]->newInstance();

				$parameters[] = [
					'name' => $attribute->getName(),
					'type' => $type->getName(),
					'required' => !$type->allowsNull(),
					'kind' => $attribute::class,
				];
			}

			$attrs = $param->getAttributes(CurrentUser::class);
			if (!empty($attrs)) {
				$attribute = $attrs[0]->newInstance();

				$parameters[] = [
					'name' => $param->getName(),
					'type' => $type->getName(),
					'required' => !$type->allowsNull(),
					'kind' => $attribute::class,
				];
			}
		}
		return $parameters;
	}

	/**
	 * @param array<string, class-string | string | bool>[] $parameters
	 * @param array<string, string> $pathVariables
	 * @param array<string, string> $requestParams
	 * @return mixed[]
	 */
	private function prepareArguments(
		array $parameters,
		array $pathVariables,
		array $requestParams
	): array {
		$args = [];
		foreach ($parameters as $param) {
			$dto = null;

			switch ($param['kind']) {
				case PathVariable::class:
					$dto = $pathVariables[$param['name']];
					break;
				case RequestBody::class:
					$dto = $this->mapper->map($param['type'], $_POST);
					break;
				case RequestParam::class:
					if (array_key_exists($param['name'], $requestParams)) {
						$dto = $requestParams[$param['name']];
					} elseif ($param['required']) {
						throw new MappingException();
					}
					break;
				case RequestFile::class:
					if (array_key_exists($param['name'], $_FILES)) {
						if ($_FILES[$param['name']]['error']
						|| empty($_FILES[$param['name']]['tmp_name'])) {
							throw new MappingException();
						}
						$dto = $_FILES[$param['name']]['tmp_name'];
					} elseif ($param['required']) {
						throw new MappingException();
					}
					break;
				case CurrentUser::class:
					if (array_key_exists('user_id', $_SESSION)) {
						$dto = $this->sessionService->getUser();
					} elseif ($param['required']) {
						throw new UnauthorizedException('Please Login to access this page.');
					}
					break;
			}

			$args[] = $dto;
		}

		return $args;
	}

	/**
	 * @return string[]
	 */
	public static function capturePathVariables(
		string $routePattern,
		string $requestPath
	): array {
		$regexPattern = preg_replace(
			'/\{(\w+)\}/',
			'(?P<\1>[^/]+)',
			$routePattern
		);
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

	public function addController(object $controller): void
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
		#[SensitiveParameter] string $uri,
		string $method
	): string {
		$urlParts = parse_url($uri);

		$requestPath = $urlParts['path'];

		if (array_key_exists('query', $urlParts)) {
			parse_str($urlParts['query'], $requestParams);
		} else {
			$requestParams = [];
		}

		if ($method === 'POST' && array_key_exists('_method', $_POST)) {
			$method = $_POST['_method'];
		}

		if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
			if (array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)
			&& $_SERVER['HTTP_CONTENT_TYPE'] === 'application/json') {
				$_POST = json_decode(file_get_contents('php://input'), associative: true);
			}

			if (!array_key_exists('_token', $_POST)
			|| !$this->sessionService->verifyCsrfToken($_POST['_token'])) {
				throw new InvalidCsrfTokenException();
			}
		}

		foreach ($this->routes as $route) {
			if ($route['method'] === $method) {
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
