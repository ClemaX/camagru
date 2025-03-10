<?php

namespace App\Services;

use App\Entities\Post;
use App\Entities\User;
use App\Exceptions\InternalException;
use App\Exceptions\ValidationException;
use App\Repositories\PostRepository;
use App\Services\DTOs\PostCreationDTO;
use DateTime;
use SensitiveParameter;
use XMLParser;

require_once __DIR__ . '/../Entities/Post.php';
require_once __DIR__ . '/../Repositories/PostRepository.php';

define('XML_NS_SVG', 'http://www.w3.org/2000/svg');
define('XML_NS_XLINK', 'http://www.w3.org/1999/xlink');
define('XML_NS_XLINK_PREFIX', 'xLink');

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
	'href' => [
		'required' => true,
		'namespace' => XML_NS_XLINK,
		'prefix' => XML_NS_XLINK_PREFIX,
	],
]);

class PostService
{
	private readonly string $storageDirectory;
	private readonly string $externalStorageUrl;
	private readonly array $allowedImageBaseUrls;
	private readonly string $bucketId;
	private readonly ?XMLParser $xmlParser;

	public function __construct(
		private PostRepository $postRepository,
		array $config,
		?XMLParser $xmlParser = null,
	) {
		$this->externalStorageUrl = $config['STORAGE_EXTERNAL_URL'];
		$this->bucketId = $config['POST_PICTURE_BUCKET_ID'];

		$this->allowedImageBaseUrls = [
			'data:image/png;base64,',
			$config['EXTERNAL_URL'],
			$this->externalStorageUrl,
		];

		$this->storageDirectory = $config['STORAGE_DIRECTORY'] . '/' . $this->bucketId;
		$this->xmlParser = ($xmlParser !== null)
			? $xmlParser : xml_parser_create_ns();
	}

	/** @return Post[] */
	public function getAll(): array
	{
		return array_map(function ($post) {
			$post->pictureUrl = $this->externalStorageUrl . '/'
				. $this->bucketId . '/'
				. $post->id . '/';

			return $post;
		}, $this->postRepository->findAll('created_at', 'DESC'));
	}

	public function post(
		#[SensitiveParameter] User $author,
		PostCreationDTO $dto,
		array $pictureFile
	): Post {
		$pictureData = file_get_contents($pictureFile['tmp_name']);

		$xmlWriter = xmlwriter_open_memory();

		if ($xmlWriter === false) {
			throw new InternalException();
		}

		// xmlwriter_set_indent($xmlWriter, true);
		// $res = xmlwriter_set_indent_string($xmlWriter, ' ');

		xmlwriter_start_document($xmlWriter, '1.0', 'UTF-8');

		$svgKey = strtoupper(XML_NS_SVG) . ':SVG';
		$imageKey = strtoupper(XML_NS_SVG) . ':IMAGE';

		// xmlwriter_end_attribute($xmlWriter);

		$inSvg = false;
		$inImage = false;
		$imageUniqueAttributes = [];
		$layers = [];

		xml_set_element_handler(
			$this->xmlParser,
			function ($parser, $name, $attributes) use (
				&$xmlWriter,
				&$inSvg,
				&$inImage,
				&$imageUniqueAttributes,
				&$layers,
				$svgKey,
				$imageKey,
			) {
				// echo "Start ", $name, ": ", var_export($attributes), PHP_EOL;

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
							throw new ValidationException([[
								'property' => $name,
								'error' => 'IMAGE must have an SVG parent',
								'constraints' => [
									'allowedParents' => ['SVG'],
								],
							]]);
						}

						if ($inImage) {
							throw new ValidationException([[
								'property' => $name,
								'error' => 'IMAGE cannot be nested',
								'constraints' => [
									'nested' => false,
								],
							]]);
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

								if (array_key_exists('numeric', $constraints) && $constraints['numeric']
								&& !is_numeric($attributeValue)) {
									throw new ValidationException([[
										'property' => "$name.$attributeKey",
										'error' => "IMAGE $attributeKey attribute value must be numeric",
										'constraints' => [
											'numeric' => $constraints['numeric'],
										],
									]]);
								}

								if (array_key_exists('pattern', $constraints) && !preg_match($constraints['pattern'], $attributeValue)) {
									throw new ValidationException([[
										'property' => "$name.$attributeKey",
										'error' => "IMAGE $attributeKey attribute value is invalid",
										'constraints' => [
											'pattern' => $constraints['pattern'],
										],
									]]);
								}

								if (array_key_exists('unique', $constraints) && $constraints['unique']) {
									if (array_key_exists($attributeKey, $imageUniqueAttributes)
									&& in_array($attributeValue, $imageUniqueAttributes[$attributeKey])) {
										throw new ValidationException([[
											'property' => "$name.$attributeKey",
											'error' => "IMAGE $attributeKey attribute value must be unique",
											'constraints' => [
												'unique' => $constraints['unique'],
											],
										]]);
									}

									$imageUniqueAttributes[$attributeKey][] = $attributeValue;
								}

								if ($attributeName === 'href') {
									if (str_starts_with($attributeValue, 'data:image/png;base64,')) {

										$layerId = $attributes['ID'];
										$image = @imagecreatefrompng($attributeValue);

										if ($image === false) {
											throw new ValidationException([[
												'property' => "$name.$attributeKey",
												'error' => "IMAGE $attributeKey attribute value data URL is invalid",
												'constraints' => [
													'type' => 'image/png',
												],
											]]);
										}

										$layers[$layerId] = $image;

										$attributeValue = $layerId . '.png';
									} else {
										if (!array_any(
											$this->allowedImageBaseUrls,
											fn ($allowedUrl, $key) =>
												str_starts_with($attributeValue, $allowedUrl)
										)) {
											throw new ValidationException([[
												'property' => "$name.$attributeKey",
												'error' => "IMAGE $attributeKey attribute value URL is invalid",
												'constraints' => [
													'startsWith' => $this->allowedImageBaseUrls,
												],
											]]);
										}
									}
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

				// echo "End ", $name, PHP_EOL;
			}
		);

		xml_parse($this->xmlParser, $pictureData, true);

		xmlwriter_end_document($xmlWriter);

		$post = new Post();

		$post->author = $author;
		$post->title = $dto->title;
		$post->description = $dto->description;

		$post->createdAt = new DateTime();
		$post->updatedAt = new DateTime();

		$post = $this->postRepository->save($post);

		$svgData =  xmlwriter_output_memory($xmlWriter);

		// TODO: Use transactions to commit instead of reverting save

		if (!file_exists($this->storageDirectory) && !mkdir($this->storageDirectory, permissions: 0755, recursive: true)) {
			$this->postRepository->delete($post->id);
			throw new InternalException();
		}

		$postDirectory = $this->storageDirectory . '/' . $post->id;

		if (file_exists($postDirectory) || !mkdir($postDirectory, permissions: 0755, recursive: true)) {
			$this->postRepository->delete($post->id);
			throw new InternalException();
		}

		if (file_put_contents($postDirectory . '/index.svg', $svgData) === false) {
			$this->postRepository->delete($post->id);
			throw new InternalException();
		}

		foreach ($layers as $layerId => $layer) {
			if (!imagepng($layer, $postDirectory . '/' . $layerId . '.png')) {
				$this->postRepository->delete($post->id);
				unlink($postDirectory);
				throw new InternalException();
			}
		}

		return $post;
	}

	// public function update(PostUpdateDTO $dto): User
	// {
	// 	throw new InternalException("Not implemented yet");
	// }
}
