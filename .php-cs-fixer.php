<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = Finder::create()
	->in(__DIR__);

$config = new Config();

$config->setRules([
		'@PSR2' => true,
		'indentation_type' => true,
	])
	->setIndent("\t")
	->setLineEnding("\n")
	->setFinder($finder)
	->setParallelConfig(ParallelConfigFactory::detect());

return $config;
