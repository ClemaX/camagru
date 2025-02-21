<?php

namespace App;

use Exception;

class Renderer
{
    public function __construct(private readonly string $templatePath)
    {
    }

    private static function processParameters(string $content, array $params): string
    {
        foreach ($params as $key => $value) {
            $content = str_replace(
                "{{ \$$key }}",
                $value != null ? htmlspecialchars($value) : "",
                $content
            );
        }

        return $content;
    }

    private static function processIfStatements($content, $params)
    {
        $pattern = '/@if\s*\((.*?)\)(.*?)(?:@else(.*?))?@endif/s';
        return preg_replace_callback($pattern, function ($matches) use ($params) {
            $condition = $matches[1];
            $ifBlock = $matches[2];
            $elseBlock = isset($matches[3]) ? $matches[3] : '';

            foreach ($params as $key => $value) {
                $condition = str_replace("\$$key", var_export($value, true), $condition);
            }

            $result = eval("return $condition;");
            return $result ? $ifBlock : $elseBlock;
        }, $content);
    }

    private static function processForLoops($content, $params)
    {
        $pattern = '/@for\s*\((.*?)\)(.*?)@endfor/s';
        return preg_replace_callback($pattern, function ($matches) use ($params) {
            $loopDefinition = $matches[1];
            $loopContent = $matches[2];

            if (preg_match('/^\$(\w+)\s+as\s+\$(\w+)$/', $loopDefinition, $arrayMatch)) {
                $arrayName = $arrayMatch[1];
                $itemName = $arrayMatch[2];

                if (!isset($params[$arrayName]) || !is_array($params[$arrayName])) {
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

            list($variable, $start, $end) = sscanf($loopDefinition, '$%s = %d to %d');
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
        }, $content);
    }


    public static function render(string $template, array $params = [])
    {
        $templateFile = __DIR__ . '/Views/' . $template . '.php';

        if (!file_exists($templateFile)) {
            throw new Exception("Template file not found: $templateFile");
        }

        $content = file_get_contents($templateFile);

        $content = self::processIfStatements($content, $params);
        $content = self::processForLoops($content, $params);

        $content = self::processParameters($content, $params);

        return $content;
    }
}
