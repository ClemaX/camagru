<?php

namespace App;

use App\Attributes\Validation\ValidationInterface;
use ReflectionAttribute;
use ReflectionObject;

class Validator
{
	public function validate(object $object): array
	{
		$errors = [];
		$reflection = new ReflectionObject($object);
		$properties = $reflection->getProperties();

		foreach ($properties as $property) {
			$attributes = $property->getAttributes(
				ValidationInterface::class,
				ReflectionAttribute::IS_INSTANCEOF
			);

			foreach ($attributes as $attribute) {
				$validator = $attribute->newInstance();
				$value = $property->getValue($object);
				$error = $validator->validate($value);
				if ($error !== null) {
					$errors[] = [
						'property' => $property->getName(),
						'error' => $error,
						'constraints' => $validator->getConstraints(),
					];
				}
			}
		}

		return $errors;
	}
}
