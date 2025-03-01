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

        $dto = new $dtoClass();

        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            $key = $property->name;

            if (!array_key_exists($key, $data)) {
                throw new MappingException();
            }

            $dto->$key = $data[$key];
        }

        $errors = $this->validator->validate($dto);

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $dto;
    }
}
