<?php

namespace App;

use App\Exceptions\InternalException;
use App\Exceptions\ValidationException;
use Exception;

define('XML_NS_SVG', 'http://www.w3.org/2000/svg');
define('XML_NS_XLINK', 'http://www.w3.org/1999/xlink');
define('XML_NS_XLINK_PREFIX', 'xlink');

define('SVG_WIDTH', 1080);
define('SVG_HEIGHT', 1080);
define('SVG_VIEWBOX', "0 0 1080 1080");
define('SVG_PRESERVEASPECTRATIO', "xMidYMid");

define('SVG_IMAGE_ATTRIBUTES', [
	'id' => [
		'required' => true,
		'unique' => true,
		'pattern' => '/^[0-9a-zA-Z_\-]{1,36}$/',
	],
	'x' => [
		'required' => true,
		'numeric' => true,
	],
	'y' => [
		'required' => true,
		'numeric' => true,
	],
	'width' => [
		'required' => true,
		'numeric' => true,
	],
	'height'=> [
		'required' => true,
		'numeric' => true,
	],
	'preserveAspectRatio' => [
		'required' => false,
		'pattern' => '/^none$/',
	],
	'href' => [
		'required' => true,
		'namespace' => XML_NS_XLINK,
		'prefix' => XML_NS_XLINK_PREFIX,
	],
]);


class SvgSanitizer
{

	public function __construct(private readonly array $allowedImageBaseUrls)
	{
	}

	public static function validateImageAttribute(
		array $attributes,
		array &$imageUniqueAttributes,
		string $key,
		array $constraints,
	) {
		$attributeValue = $attributes[$key];

		$errors = [];

		if (array_key_exists('numeric', $constraints) && $constraints['numeric']
		&& !is_numeric($attributeValue)) {
			$errors[] = [
				'property' => "IMAGE.$key",
				'error' => "IMAGE $key attribute value must be numeric",
				'constraints' => [
					'numeric' => $constraints['numeric'],
				],
			];
		}

		if (array_key_exists('pattern', $constraints) && !preg_match($constraints['pattern'], $attributeValue)) {
			$errors[] = [
				'property' => "IMAGE.$key",
				'error' => "IMAGE $key attribute value is invalid",
				'constraints' => [
					'pattern' => $constraints['pattern'],
				],
			];
		}

		if (array_key_exists('unique', $constraints) && $constraints['unique']) {
			if (array_key_exists($key, $imageUniqueAttributes)
			&& in_array($attributeValue, $imageUniqueAttributes[$key])) {
				$errors[] = [
					'property' => "IMAGE.$key",
					'error' => "IMAGE $key attribute value must be unique",
					'constraints' => [
						'unique' => $constraints['unique'],
					],
				];
			} else {
				$imageUniqueAttributes[$key][] = $attributeValue;
			}
		}

		return $errors;
	}

	public function sanitizeImageSource(
		string $layerId,
		string $source,
		string $outputDirectory,
		array &$writtenFilenames,
	): string {
		if (str_starts_with($source, 'data:image/png;base64,')) {
			$image = @imagecreatefrompng($source);

			if ($image === false) {
				throw new ValidationException([[
					'property' => "IMAGE.href",
					'error' => "IMAGE href attribute value data URL is invalid",
					'constraints' => [
						'type' => 'image/png',
					],
				]]);
			}

			$filename = $outputDirectory . '/' . $layerId . '.png';

			if (!imagepng($image, $filename)) {
				throw new InternalException('Could not write PNG image');
			}

			$writtenFilenames[] = $filename;

			$source = $layerId . '.png';
		} else {
			if (!array_any(
				$this->allowedImageBaseUrls,
				fn ($allowedUrl, $key) =>
					str_starts_with($source, $allowedUrl)
			)) {
				throw new ValidationException([[
					'property' => "IMAGE.href",
					'error' => "IMAGE href attribute value URL is invalid",
					'constraints' => [
						'startsWith' => $this->allowedImageBaseUrls,
					],
				]]);
			}
		}

		return $source;
	}

	public function sanitize(string $inputFilename, string $outputDirectory)
	{
		$outputFilename = $outputDirectory . '/index.svg';

		$xmlParser = xml_parser_create_ns();

		$svgData = file_get_contents($inputFilename);
		if ($svgData === false) {
			throw new InternalException("Failed to open input file");
		}

		$xmlWriter = xmlwriter_open_uri($outputFilename);

		if ($xmlWriter === false) {
			throw new InternalException("Failed to create xml writer");
		}

		xmlwriter_start_document($xmlWriter, '1.0', 'UTF-8');

		$svgKey = strtoupper(XML_NS_SVG) . ':SVG';
		$imageKey = strtoupper(XML_NS_SVG) . ':IMAGE';

		$inSvg = false;
		$inImage = false;
		$imageUniqueAttributes = [];
		$writtenFilenames = [$outputFilename];

		xml_set_element_handler(
			$xmlParser,
			function ($parser, $name, $attributes) use (
				&$xmlWriter,
				&$inSvg,
				&$inImage,
				&$imageUniqueAttributes,
				&$writtenFilenames,
				$outputDirectory,
				$svgKey,
				$imageKey,
			) {
				$errors = [];

				switch ($name) {
					case $svgKey:
						if ($inSvg) {
							throw new ValidationException([[
								'property' => $name,
								'error' => 'Exactly 1 SVG element is allowed',
								'constraints' => [
									'count' => 1,
								],
							]]);
						}

						if (!array_key_exists('WIDTH', $attributes)
						|| $attributes['WIDTH'] != SVG_WIDTH
						|| !array_key_exists('HEIGHT', $attributes)
						|| $attributes['HEIGHT'] != SVG_HEIGHT) {
							throw new ValidationException([[
								'property' => $name,
								'error' => 'SVG element has invalid dimensions',
								'constraints' => [
									'width' => SVG_WIDTH,
									'height' => SVG_HEIGHT,
								],
							]]);
						}

						$inSvg = true;

						xmlwriter_start_element_ns($xmlWriter, null, 'svg', XML_NS_SVG);
						xmlwriter_write_attribute($xmlWriter, 'width', SVG_WIDTH);
						xmlwriter_write_attribute($xmlWriter, 'height', SVG_HEIGHT);
						xmlwriter_write_attribute($xmlWriter, 'viewBox', SVG_VIEWBOX);
						xmlwriter_write_attribute($xmlWriter, 'preserveAspectRatio', SVG_PRESERVEASPECTRATIO);
						xmlwriter_write_attribute($xmlWriter, 'xmlns:' . XML_NS_XLINK_PREFIX, XML_NS_XLINK);

						break;

					case $imageKey:
						if (!$inSvg) {
							$errors = [[
								'property' => $name,
								'error' => 'IMAGE must have an SVG parent',
								'constraints' => [
									'allowedParents' => ['SVG'],
								],
							]];
						} elseif ($inImage) {
							$errors = [[
								'property' => $name,
								'error' => 'IMAGE cannot be nested',
								'constraints' => [
									'nested' => false,
								],
							]];
						}

						if (!empty($errors)) {
							throw new ValidationException($errors);
						}

						xmlwriter_start_element($xmlWriter, 'image');

						foreach (SVG_IMAGE_ATTRIBUTES as $attributeName => $constraints) {
							$attributeKey = array_key_exists('namespace', $constraints)
							? strtoupper($constraints['namespace']) . ':' . strtoupper($attributeName)
							: strtoupper($attributeName);

							if (!array_key_exists($attributeKey, $attributes)) {
								if ($constraints['required']) {
									throw new ValidationException([[
										'property' => "$name.$attributeKey",
										'error' => "IMAGE must have an $attributeKey attribute",
										'constraints' => [
											'required' => $constraints['required'],
										],
									]]);
								}
							} else {
								$attributeValue = $attributes[$attributeKey];

								$errors = $this->validateImageAttribute(
									$attributes,
									$imageUniqueAttributes,
									$attributeKey,
									$constraints
								);
								if (!empty($errors)) {
									throw new ValidationException($errors);
								}

								if ($attributeName === 'href') {
									$attributeValue = $this->sanitizeImageSource(
										$attributes['ID'],
										$attributeValue,
										$outputDirectory,
										$writtenFilenames
									);
								}

								if (array_key_exists('namespace', $constraints)) {
									xmlwriter_write_attribute_ns(
										$xmlWriter,
										$constraints['prefix'],
										$attributeName,
										null,
										$attributeValue
									);
								} else {
									xmlwriter_write_attribute(
										$xmlWriter,
										$attributeName,
										$attributeValue
									);
								}
							}

							if (!empty($errors)) {
								break;
							}
						}

						$inImage = true;

						break;

					default:
						throw new ValidationException([[
								'property' => $name,
								'error' => 'Only SVG and IMAGE elements are allowed',
								'constraints' => [
									'allowedElements' => ['SVG', 'IMAGE'],
								],
							]]);

						break;
				}
			},
			function ($parser, $name) use (
				&$xmlWriter,
				&$inSvg,
				&$inImage,
				$svgKey,
				$imageKey,
			) {
				switch ($name) {
					case $svgKey:
						$inSvg = false;
						break;

					case $imageKey:
						$inImage = false;
						break;
				}

				xmlwriter_end_element($xmlWriter);
			}
		);

		try {
			$success = xml_parse($xmlParser, $svgData, true);
		} catch (Exception $e) {
			xml_parser_free($xmlParser);
			xmlwriter_end_document($xmlWriter);

			foreach ($writtenFilenames as $filename) {
				unlink($filename);
			}

			throw $e;
		}

		xml_parser_free($xmlParser);
		xmlwriter_end_document($xmlWriter);

		if (!$success) {
			throw new ValidationException([[
				'property' => 'picture',
				'error' => 'Invalid XML',
			]]);
		}
	}
}
