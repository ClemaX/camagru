<?php

namespace App;

use App\Exceptions\MappingException;
use App\Exceptions\ValidationException;
use ReflectionClass;
use BackedEnum;
use ReflectionNamedType;

class Mapper
{
	public function __construct(private Validator $validator = new Validator())
	{
	}

	/**
	 * @param array<string, bool | int | string | BackedEnum | null> $data
	 */
	public function map(string $dtoClass, array $data): object
	{
		$reflectionClass = new ReflectionClass($dtoClass);
		$properties = $reflectionClass->getProperties();

		$dto = new $dtoClass();

		foreach ($properties as $property) {
			$type = $property->getType();
			assert($type instanceof ReflectionNamedType);
			$key = $property->name;


			if ($type->isBuiltin() && $type->getName() === 'bool') {
				$property->setValue($dto, array_key_exists($key, $data));
			} elseif (array_key_exists($key, $data)) {
				$property->setValue($dto, $data[$key]);
			} elseif ($type->allowsNull()) {
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
