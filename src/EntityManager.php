<?php

namespace App;

use App\Attributes\Entity\Column;
use App\Attributes\Entity\Entity;
use App\Attributes\Entity\Id;
use App\Attributes\Entity\OneToOne;
use App\Attributes\Entity\PrimaryKeyJoinColumn;
use App\Exceptions\InternalException;
use BackedEnum;
use PDO;
use PDOStatement;
use ReflectionClass;
use ReflectionProperty;

require_once __DIR__ . '/Attributes/Entity/Column.php';
require_once __DIR__ . '/Attributes/Entity/Entity.php';
require_once __DIR__ . '/Attributes/Entity/Id.php';
require_once __DIR__ . '/Attributes/Entity/OneToOne.php';
require_once __DIR__ . '/Attributes/Entity/PrimaryKeyJoinColumn.php';
require_once __DIR__ . '/Exceptions/InternalException.php';

require_once __DIR__ . '/Exceptions/InternalException.php';
require_once __DIR__ . '/Exceptions/InternalException.php';

class EntityManager
{
	public function __construct(protected readonly PDO $pdo)
	{
	}

	protected static function getTableName(string $modelClass): string
	{
		$reflectionClass = new ReflectionClass($modelClass);

		$entityAttributes = $reflectionClass->getAttributes(Entity::class);

		if (empty($entityAttributes)) {
			throw new InternalException("Entity class must have an Entity attribute");
		}

		return $entityAttributes[0]->newInstance()->tableName;
	}

	private static function getIdColumn(string $modelClass): ReflectionProperty
	{
		$reflectionClass = new ReflectionClass($modelClass);

		$idProperties = array_filter(
			$reflectionClass->getProperties(),
			function ($property) {
				return !empty($property->getAttributes(Id::class));
			}
		);

		if (empty($idProperties)) {
			throw new InternalException("Entity class must have a property with an Id attribute");
		}

		$idProperty = $idProperties[0];

		$columnAttributes = $idProperty->getAttributes(Column::class);
		if (empty($columnAttributes)) {
			throw new InternalException("Entity Id property must have a Column attribute");
		}

		return $idProperty;
	}

	private static function getIdColumnAttribute(string $modelClass): Column
	{
		$idProperty = self::getIdColumn($modelClass);

		$columnAttribute = $idProperty->getAttributes(Column::class)[0];

		return $columnAttribute->newInstance();
	}

	/** @return ReflectionProperty[] */
	private static function getColumns(string $modelClass): array
	{
		$reflectionClass = new ReflectionClass($modelClass);

		$columnProperties = array_filter(
			$reflectionClass->getProperties(),
			function ($property) {
				return !empty($property->getAttributes(Column::class));
			}
		);

		return $columnProperties;
	}

	/** @return ReflectionProperty[] */
	private static function getOneToOneRelations(string $modelClass): array
	{
		$reflectionClass = new ReflectionClass($modelClass);

		$columnProperties = array_filter(
			$reflectionClass->getProperties(),
			function ($property) {
				return !empty($property->getAttributes(OneToOne::class));
			}
		);

		return $columnProperties;
	}

	private static function getParamType($value): int
	{
		if (is_bool($value)) {
			return PDO::PARAM_BOOL;
		} elseif (is_int($value)) {
			return PDO::PARAM_INT;
		} elseif (is_null($value)) {
			return PDO::PARAM_NULL;
		} else {
			return PDO::PARAM_STR;
		}
	}

	private static function bindParameters(PDOStatement $stmt, array $data)
	{
		foreach ($data as $key => $value) {
			if ($value instanceof BackedEnum) {
				$type = PDO::PARAM_STR;
				$value = $value->value;
			} else {
				$type = self::getParamType($value);
			}
			$stmt->bindValue(":$key", $value, $type);
		}
	}

	private function load(array $data, string $modelClass): object
	{
		$columnProperties = self::getColumns($modelClass);
		$idColumnProperty = self::getIdColumn($modelClass);

		$model = new $modelClass();

		foreach ($columnProperties as $property) {
			$column = $property->getAttributes(Column::class)[0]->newInstance();
			if (enum_exists($property->getType()->getName())) {
				$enumType = $property->getType()->getName();
				$enumValue = $enumType::from($data[$column->name]);
				$property->setValue($model, $enumValue);
			} else {
				$property->setValue($model, $data[$column->name]);
			}
		}

		$id = $idColumnProperty->getValue($model);

		$oneToOneProperties = self::getOneToOneRelations($modelClass);

		foreach ($oneToOneProperties as $property) {
			$propertyType = $property->getType();

			if ($propertyType->isBuiltin()) {
				throw new InternalException('Unsupported relation property type');
			}

			$propertyClass = $propertyType->getName();

			$primaryKeyJoinColumns = $property->getAttributes(PrimaryKeyJoinColumn::class);
			if (empty($primaryKeyJoinColumns)) {
				throw new InternalException('Unsupported relation type');
			}

			$propertyIdColumn = self::getIdColumnAttribute($propertyClass);

			$stmt = $this->pdo->prepare("SELECT * FROM " . self::getTableName($propertyClass) . " WHERE " . $propertyIdColumn->name . " = :id");
			$stmt->execute(['id' => $id]);
			$result = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$result && !$propertyType->allowsNull()) {
				throw new InternalException('Non-nullable relation could not be found');
			}

			$propertyModel = $this->load($result, $propertyClass);

			$property->setValue($model, $propertyModel);
		}

		return $model;
	}

	private function dump(object $model, string $modelClass): array
	{
		$columnProperties = $this->getColumns($modelClass);
		$data = [];

		foreach ($columnProperties as $property) {
			$column = $property->getAttributes(Column::class)[0]->newInstance();

			$data[$column->name] = $property->getValue($model);
		}

		return $data;
	}

	public function findBy(array $criteria, $modelClass): ?object
	{
		if (empty($criteria)) {
			throw new InternalException("Criteria cannot be empty");
		}

		$conditions = implode(
			' AND ',
			array_map(fn ($field) => "$field = :$field", array_keys($criteria))
		);

		$stmt = $this->pdo->prepare("SELECT * FROM " . self::getTableName($modelClass) ." WHERE " . $conditions);

		if ($stmt === false) {
			throw new InternalException();
		}

		self::bindParameters($stmt, $criteria);

		$stmt->execute();

		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$result) {
			return null;
		}

		return $this->load($result, $modelClass);
	}

	public function findById(int $id, string $modelClass): ?object
	{
		$idColumn = $this->getIdColumnAttribute($modelClass);

		$stmt = $this->pdo->prepare("SELECT * FROM " . self::getTableName($modelClass) ." WHERE " . $idColumn->name . " = :id");

		if ($stmt === false) {
			throw new InternalException();
		}

		$stmt->execute(['id' => $id]);
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$result) {
			return null;
		}

		return $this->load($result, $modelClass);
	}

	public function findAll($modelClass): array
	{
		$stmt = $this->pdo->query("SELECT * FROM " . self::getTableName($modelClass) ."");

		if ($stmt === false) {
			throw new InternalException();
		}

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		return array_map(fn ($data) => $this->load($data, $modelClass), $results);
	}

	public function save(object $model, string $modelClass, ?int $id = null): int
	{
		$data = $this->dump($model, $modelClass);

		$idColumn = $this->getIdColumnAttribute($modelClass);

		if ($data[$idColumn->name] === 0) {
			if ($id !== null) {
				$data[$idColumn->name] = $id;
			} else {
				unset($data[$idColumn->name]);
			}
		}

		$fields = implode(', ', array_keys($data));
		$placeholders = ':' . implode(', :', array_keys($data));

		$stmt = $this->pdo->prepare("INSERT INTO " . self::getTableName($modelClass) ." ($fields) VALUES ($placeholders)");

		if ($stmt === false) {
			throw new InternalException();
		}

		self::bindParameters($stmt, $data);

		$success = $stmt->execute();

		if (!$success) {
			throw new InternalException();
		}

		$insertedId = (int)$this->pdo->lastInsertId();

		$oneToOneProperties = self::getOneToOneRelations($modelClass);

		// TODO: Use transactions to rollback if relation insertion fails

		foreach ($oneToOneProperties as $property) {
			$this->save(
				$property->getValue($model),
				$property->getType()->getName(),
				$insertedId,
			);
		}

		return $insertedId;
	}

	public function merge(object $model, string $modelClass): bool
	{
		$data = self::dump($model, $modelClass);

		$idColumnProperty = self::getIdColumn($modelClass);
		$idColumn = $idColumnProperty->getAttributes(Column::class)[0]->newInstance();

		$id = $idColumnProperty->getValue($model);
		unset($data[$idColumn->name]);

		$setClause = implode(', ', array_map(fn ($field) => "$field = :$field", array_keys($data)));

		$stmt = $this->pdo->prepare("UPDATE " . self::getTableName($modelClass) ." SET $setClause WHERE " . $idColumn->name . " = :id");

		self::bindParameters($stmt, $data);
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);

		return $stmt->execute();
	}

	public function delete(int $id, string $modelClass): bool
	{
		$stmt = $this->pdo->prepare("DELETE FROM " . self::getTableName($modelClass) . " WHERE id = :id");
		return $stmt->execute(['id' => $id]);
	}
}
