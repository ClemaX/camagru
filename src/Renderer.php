<?php

namespace App;

use App\Enumerations\Role;
use App\Services\UserSessionServiceInterface;
use Exception;

require_once __DIR__ . '/Enumerations/Role.php';
require_once __DIR__ . '/Services/UserSessionServiceInterface.php';

class Renderer
{
    public function __construct(
        private readonly UserSessionServiceInterface $sessionService,
        private readonly string $templateDir,
        private readonly string $baseUrlPath,
    ) {
    }

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

                foreach ($params as $key => $value) {
                    $substitute = $value != null
                        ? (
                            $key === 'content'
                            ? $value
                            : htmlspecialchars($value)
                        )
                        : "";

                    $path = str_replace("{\$$key}", $substitute, $path);
                }

                return rtrim($baseUrlPath, '/') . '/' . ltrim($path, '/');
            },
            $content
        );
    }

    private static function processIfStatements(
        string $content,
        array $params
    ): string {
        $pattern = '/@if\s*\((.*?)\)(.*?)(?:@else(.*?))?@endif/s';
        return preg_replace_callback($pattern, function ($matches) use ($params) {
            $condition = $matches[1];
            $ifBlock = $matches[2];
            $elseBlock = isset($matches[3]) ? $matches[3] : '';

            foreach ($params as $key => $value) {
                $condition = str_replace(
                    "\$$key",
                    var_export($value, true),
                    $condition
                );
            }

            $result = eval("return $condition;");
            return $result ? $ifBlock : $elseBlock;
        }, $content);
    }

    private static function processForLoops(
        string $content,
        array $params
    ): string {
        $pattern = '/@foreach\s*\((.*?)\)(.*?)@endforeach/s';
        return preg_replace_callback(
            $pattern,
            function ($matches) use ($params) {
                $loopDefinition = $matches[1];
                $loopContent = $matches[2];

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

                    $output = '';
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
                    return $output;
                }

                list($variable, $start, $end) = sscanf(
                    $loopDefinition,
                    '$%s = %d to %d'
                );
                $output = '';
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
                return $output;
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
            function ($matches) use ($userRoles) {
                if ($userRoles === null) {
                    $user = $this->sessionService->getUser();
                    $userRoles = [$user !== null ? Role::USER : Role::GUEST];
                }

                $roles = str_getcsv($matches[1], escape: '\\');

                $ifBlock = $matches[2];
                $elseBlock = isset($matches[3]) ? $matches[3] : '';

                $hasRole = array_find(
                    $userRoles,
                    function (Role $userRole) use ($roles) {
                        return in_array($userRole->value, $roles);
                    }
                ) !== null;

                return $hasRole ? $ifBlock : $elseBlock;
            },
            $content
        );
    }

    private function processCsrf(string $content)
    {
        $csrfToken = $this->sessionService->getCsrfToken();
        $csrfField = '<input type="hidden" name="_token" value="' . htmlspecialchars($csrfToken) . '" />';

        return str_replace('@csrf', $csrfField, $content);
    }

    public function render(string $templateName, array $params = [])
    {
        $templateFile = __DIR__
            . DIRECTORY_SEPARATOR . $this->templateDir
            . DIRECTORY_SEPARATOR . $templateName . '.php';

        if (!file_exists($templateFile)) {
            throw new Exception("Template file not found: $templateFile");
        }

        $content = file_get_contents($templateFile);

        $content = $this->processRoles($content);
        $content = $this::processCsrf($content);

        $content = self::processIfStatements($content, $params);
        $content = self::processForLoops($content, $params);

        $content = self::processUrls($content, $params, $this->baseUrlPath);
        $content = self::processParameters($content, $params);

        return $content;
    }
}
