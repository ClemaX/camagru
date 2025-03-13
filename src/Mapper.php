<?php

namespace App;

use App\Exceptions\MappingException;
use App\Exceptions\ValidationException;
use ReflectionClass;

require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/Exceptions/MappingException.php';
require_once __DIR__ . '/Exceptions/ValidationException.php';

class Mapper
{
	public function __construct(private Validator $validator = new Validator())
	{
	}

	public function map(string $dtoClass, array $data): object
	{
		$reflectionClass = new ReflectionClass($dtoClass);
		$properties = $reflectionClass->getProperties();

		$dto = new $dtoClass();

		foreach ($properties as $property) {
			$key = $property->name;

			if ($property->getType()->isBuiltin() && $property->getType()->getName() === 'bool') {
				$property->setValue($dto, array_key_exists($key, $data));
			} elseif (array_key_exists($key, $data)) {
				$property->setValue($dto, $data[$key]);
			} elseif ($property->getType()->allowsNull()) {
				$property->setValue($dto, null);
			} else {
				throw new MappingException();
			}
		}

		$errors = $this->validator->validate($dto);

		if (!empty($errors)) {
			throw new ValidationException($errors);
		}

		return $dto;
	}
}
