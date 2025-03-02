<?php
use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = PhpCsFixer\Finder::create()
    /*
    ->exclude('somedir')
    maybe we should parse `./.vscode/settings.json`
    looking for "files.exclude" / "search.exclude" here?
    */
    ->in(__DIR__);

$config = new PhpCsFixer\Config();

$config->setRules([
        '@PSR2' => true,
        'indentation_type' => true,
    ])
    ->setIndent("\t")
    ->setLineEnding("\n")
    ->setFinder($finder);

return $config;
