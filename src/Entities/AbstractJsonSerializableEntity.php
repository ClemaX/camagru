<?php

namespace App\Entities;

use App\Attributes\Serialization\JsonIgnore;
use DateTime;
use JsonSerializable;
use ReflectionClass;
use ReflectionNamedType;

abstract class AbstractJsonSerializableEntity implements JsonSerializable
{
	public function jsonSerialize(): mixed
	{
		$reflection = new ReflectionClass($this);
		$properties = $reflection->getProperties();
		$result = [];

		foreach ($properties as $property) {
			$property->setAccessible(true);
			$attributes = $property->getAttributes(JsonIgnore::class);

			if (empty($attributes)) {
				$propertyType = $property->getType();

				if ($propertyType instanceof ReflectionNamedType
				&& $propertyType->getName() === DateTime::class) {
					/** @var DateTime */
					$dateTime = $property->getValue($this);
					$result[$property->getName()] = $dateTime->getTimestamp();
				} else {
					$result[$property->getName()] = $property->getValue($this);
				}
			}
		}

		return $result;
	}
}
