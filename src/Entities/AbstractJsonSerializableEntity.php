<?php

namespace App\Entities;

use App\Attributes\Serialization\JsonIgnore;
use JsonSerializable;
use ReflectionClass;

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
				$result[$property->getName()] = $property->getValue($this);
			}
		}

		return $result;
	}
}
