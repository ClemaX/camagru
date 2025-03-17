<?php

namespace App;

use App\Enumerations\Role;
use App\Services\UserSessionServiceInterface;
use Exception;

class Renderer
{
	private readonly string $appEnvironment;

	/**
	 * @param array<string, string> $config
	 */
	public function __construct(
		private readonly UserSessionServiceInterface $sessionService,
		private readonly string $templateDir,
		private readonly string $baseUrlPath,
		array $config,
	) {
		$this->appEnvironment = $config['APP_ENV'];
	}

	/**
	 * @param array<string, mixed> $params
	 */
	private static function processParameters(
		string $content,
		array $params
	): string {

		$pattern = '/{{\s*(.*?)\s*}}/s';

		return preg_replace_callback(
			$pattern,
			function ($matches) use ($params) {
				$expression = $matches[1];

				extract($params, EXTR_SKIP);

				return eval("return $expression;");
			},
			$content
		);
	}

	/**
	 * @param array<string, mixed> $params
	 */
	private static function processUrls(
		string $content,
		array $params,
		string $baseUrlPath,
	): string {
		$pattern = '/{{ url\((.*?)\) }}/s';

		return preg_replace_callback(
			$pattern,
			function ($matches) use ($params, $baseUrlPath) {
				$path = $matches[1];

				extract($params, EXTR_SKIP);

				$path = eval("return $path;");

				return rtrim($baseUrlPath, '/') . '/' . ltrim($path, '/');
			},
			$content
		);
	}

	/**
	 * @param array<string, mixed> $params
	 */
	private static function processIfStatements(
		string $content,
		array $params
	): string {
		$pattern = '/@if\s*(\((?:[^()]++|(?1))*\))(.*?)(?:@else(.*?))?@endif/s';
		return preg_replace_callback($pattern, function ($matches) use ($params) {
			$condition = $matches[1];
			$ifBlock = $matches[2];
			$elseBlock = isset($matches[3]) ? $matches[3] : '';

			extract($params, EXTR_SKIP);

			$result = eval("return $condition;");

			return $result ? $ifBlock : $elseBlock;
		}, $content);
	}

	/**
	 * @param array<string, mixed> $params
	 */
	private static function processForLoops(
		string $content,
		array $params
	): string {
		$pattern = '/@foreach\s*\((.*?)\)((?:(?!@foreach|@endforeach).|(?R))*)@endforeach/s';
		return preg_replace_callback(
			$pattern,
			function ($matches) use ($params) {
				$loopDefinition = $matches[1];
				$loopContent = $matches[2];

				$output = '';

				if (preg_match(
					'/^\$(\w+)\s+as\s+\$(\w+)$/',
					$loopDefinition,
					$arrayMatch
				)) {
					$arrayName = $arrayMatch[1];
					$itemName = $arrayMatch[2];

					if (!isset($params[$arrayName])
					|| !is_array($params[$arrayName])) {
						return '';
					}

					foreach ($params[$arrayName] as $item) {
						$iterationParams = $params;
						$iterationParams[$itemName] = $item;
						$iterationContent = $loopContent;
						$iterationContent = self::processParameters(
							$iterationContent,
							$iterationParams
						);

						$output .= $iterationContent;
					}
				} else {
					list($variable, $start, $end) = sscanf(
						$loopDefinition,
						'$%s = %s to %s'
					);

					extract($params, EXTR_SKIP);

					$start = eval("return $start;");
					$end = eval("return $end;");

					for ($i = $start; $i <= $end; $i++) {
						$iterationParams = $params;
						$iterationParams[$variable] = $i;
						$iterationContent = $loopContent;
						$iterationContent = self::processParameters(
							$iterationContent,
							$iterationParams
						);
						$output .= $iterationContent;
					}
				}

				return self::processForLoops($output, $params);
			},
			$content
		);
	}

	private function processEnv(string $content): string
	{
		$pattern = '/@env\s*\((.*?)\)(.*?)(?:@else(.*?))?@endenv/s';
		$currentEnv = $this->appEnvironment;

		return preg_replace_callback(
			$pattern,
			function ($matches) use ($currentEnv) {
				$environments = str_getcsv($matches[1], escape: '\\');

				$ifBlock = $matches[2];
				$elseBlock = isset($matches[3]) ? $matches[3] : '';

				$isMatchingEnv = array_find($environments, fn ($environment) => $environment === $currentEnv) !== null;

				return $isMatchingEnv ? $ifBlock : $elseBlock;
			},
			$content
		);
	}

	private function processRoles(string $content): string
	{
		$pattern = '/@role\s*\((.*?)\)(.*?)(?:@else(.*?))?@endrole/s';
		$userRoles = null;

		return preg_replace_callback(
			$pattern,
			function ($matches) use (&$userRoles) {
				if ($userRoles === null) {
					$user = $this->sessionService->getUser();
					$userRoles = [$user !== null ? $user->role : Role::GUEST];
				}

				$roles = str_getcsv($matches[1], escape: '\\');

				$ifBlock = $matches[2];
				$elseBlock = isset($matches[3]) ? $matches[3] : '';

				$hasRole = array_find(
					$userRoles,
					function (Role $userRole) use ($roles) {
						return in_array($userRole->name, $roles);
					}
				) !== null;

				return $hasRole ? $ifBlock : $elseBlock;
			},
			$content
		);
	}

	private function processForms(string $content): string
	{
		$csrfToken = $this->sessionService->getCsrfToken();
		$csrfField = '<input type="hidden" name="_token" value="' . htmlspecialchars($csrfToken) . '" />';

		$content = str_replace('@csrf', $csrfField, $content);
		$content = preg_replace_callback('/@method\s*\(\'(.*?)\'\)/s', function ($matches) {
			$method = $matches[1];
			return '<input type="hidden" name="_method" value="' . htmlspecialchars($method) . '" />';
		}, $content);

		return $content;
	}

	/**
	 * @param array<string, mixed> $params
	 */
	public function render(string $templateName, array $params = []): string
	{
		$templateFile = __DIR__
			. DIRECTORY_SEPARATOR . $this->templateDir
			. DIRECTORY_SEPARATOR . $templateName . '.php';

		if (!file_exists($templateFile)) {
			throw new Exception("Template file not found: $templateFile");
		}

		$content = file_get_contents($templateFile);

		$params['csrfToken'] = $this->sessionService->getCsrfToken();

		$content = $this->processEnv($content);
		$content = $this->processRoles($content);
		$content = $this::processForms($content);

		$content = self::processIfStatements($content, $params);
		$content = self::processForLoops($content, $params);

		$content = self::processUrls($content, $params, $this->baseUrlPath);
		$content = self::processParameters($content, $params);

		return $content;
	}
}
