<?php

namespace App;

use App\Attributes\Entity\Column;
use App\Attributes\Entity\Entity;
use App\Attributes\Entity\Id;
use App\Attributes\Entity\JoinColumn;
use App\Attributes\Entity\ManyToOne;
use App\Attributes\Entity\OneToOne;
use App\Attributes\Entity\PrimaryKeyJoinColumn;
use App\Exceptions\InternalException;
use BackedEnum;
use DateTime;
use InternalIterator;
use PDO;
use PDOStatement;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

require_once __DIR__ . '/Attributes/Entity/Column.php';
require_once __DIR__ . '/Attributes/Entity/Entity.php';
require_once __DIR__ . '/Attributes/Entity/Id.php';
require_once __DIR__ . '/Attributes/Entity/OneToOne.php';
require_once __DIR__ . '/Attributes/Entity/JoinColumn.php';
require_once __DIR__ . '/Attributes/Entity/ManyToOne.php';
require_once __DIR__ . '/Attributes/Entity/PrimaryKeyJoinColumn.php';
require_once __DIR__ . '/Exceptions/InternalException.php';

class EntityManager
{
	public function __construct(protected readonly PDO $pdo)
	{
	}

	/**
	 * @template EntityT of object
	 * @param class-string<EntityT> $modelClass
	 */
	protected static function getTableName(string $modelClass): string
	{
		$reflectionClass = new ReflectionClass($modelClass);

		$entityAttributes = $reflectionClass->getAttributes(Entity::class);

		if (empty($entityAttributes)) {
			throw new InternalException("Entity class must have an Entity attribute");
		}

		return $entityAttributes[0]->newInstance()->tableName;
	}

	/**
	 * @template EntityT of object
	 * @param class-string<EntityT> $modelClass
	 */
	private static function getIdProperty(string $modelClass): ReflectionProperty
	{
		$reflectionClass = new ReflectionClass($modelClass);

		$idProperties = array_filter(
			$reflectionClass->getProperties(),
			fn ($property) => !empty($property->getAttributes(Id::class)),
		);

		if (count($idProperties) !== 1) {
			throw new InternalException("Entity class must have exactly one property with an Id attribute");
		}

		$idProperty = $idProperties[0];
		$type = $idProperty->getType();

		if ($type === null || !$type instanceof ReflectionNamedType) {
			throw new InternalException("Entity Id must have a named type");
		}

		if (!$type->isBuiltin()) {
			$reflectionClass = new ReflectionClass($type->getName());

			$idProperties = $reflectionClass->getProperties();
		}

		if (array_any(
			$idProperties,
			fn ($property) => empty($property->getAttributes(Column::class))
		)) {
			throw new InternalException("Entity Id properties must have a Column attribute");
		}

		return $idProperty;
	}

	/**
	 * @return ReflectionProperty[]
	 */
	private static function getIdColumnProperties(string $modelClass): array
	{
		$idProperty = self::getIdProperty($modelClass);

		if (!$idProperty->getType()->isBuiltin()) {
			$reflectionClass = new ReflectionClass($idProperty->getType()->getName());

			$idProperties = $reflectionClass->getProperties();
		} else {
			$idProperties = [$idProperty];
		}

		return $idProperties;
	}

	/** @return ReflectionProperty[] */
	private static function getColumns(string $modelClass): array
	{
		$reflectionClass = new ReflectionClass($modelClass);

		$columnProperties = array_filter(
			$reflectionClass->getProperties(),
			fn ($property) => !empty($property->getAttributes(Column::class))
		);

		if (array_any($columnProperties, function ($property) {
			$type = $property->getType();
			return $type === null || !$type instanceof ReflectionNamedType;
		})) {
			throw new InternalException("Column property must have a named type");
		}

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

		if (array_any($columnProperties, fn ($property) => $property->getType()->isBuiltin())) {
			throw new InternalException('Unsupported relation property type');
		}

		return $columnProperties;
	}

	/** @return ReflectionProperty[] */
	private static function getManyToOneRelations(string $modelClass): array
	{
		$reflectionClass = new ReflectionClass($modelClass);

		$columnProperties = array_filter(
			$reflectionClass->getProperties(),
			function ($property) {
				return !empty($property->getAttributes(ManyToOne::class));
			}
		);

		return $columnProperties;
	}

	private static function getParamType(mixed $value): int
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

	/**
	 * @param array<string, string | array<string, string>> $criteria
	 */
	private static function formulateConditions(array $criteria): string
	{
		$conditions = implode(
			' AND ',
			array_map(
				function (string $column, string | array $value) {
					if (is_array($value)) {
						if (empty($value)) {
							throw new InternalException('Criteria array cannot be empty');
						}

						$columns = array_keys($value);

						return '(' . implode(', ', $columns) . ')'
							. ' = (:' . implode(', :', $columns) . ')';
					} else {
						return $column . ' = :' . $column;
					}
				},
				array_keys($criteria),
				array_values($criteria)
			)
		);

		return $conditions;
	}

	/**
	 * @template EntityT of object
	 * @param class-string<EntityT>
	 * @return array<string, string | array<string, string>>
	 */
	private static function getIdCriteria(int|object $id, string $modelClass): array
	{
		$criteria = [];

		$idProperty = self::getIdProperty($modelClass);
		$idType = $idProperty->getType();

		if ($idType->isBuiltin()) {
			$idColumn =
				$idProperty->getAttributes(Column::class)[0]->newInstance();

			$criteria = [
				$idColumn->name => $id,
			];
		} else {
			$idClassName = $idType->getName();

			if (!is_a($id, $idClassName)) {
				throw new InternalException("Entity Id type does not match");
			}

			$criteria = self::dump($id, $idClassName);
		}

		return $criteria;
	}

	/**
	 * @template EntityT of object
	 * @param class-string<EntityT> $modelClass
	 */
	private function fetchOneToOneRelations(object $model, string $modelClass)
	{
		$idProperty = self::getIdProperty($modelClass);
		$id = $idProperty->getValue($model);

		$oneToOneProperties = self::getOneToOneRelations($modelClass);

		foreach ($oneToOneProperties as $property) {
			$propertyType = $property->getType();

			$propertyClass = $propertyType->getName();

			$primaryKeyJoinColumns = $property->getAttributes(PrimaryKeyJoinColumn::class);
			if (empty($primaryKeyJoinColumns)) {
				throw new InternalException('Unsupported relation type');
			}

			$propertyIdProperty = self::getIdProperty($propertyClass);
			if ($idProperty->getType()->getName() !== $propertyIdProperty->getType()->getName()) {
				throw new InternalException('PrimaryKeyJoinColumn related Entity ID properties must have the same type');
			}

			$propertyModel = $this->findById($id, $propertyClass);

			if ($propertyModel === null && !$propertyType->allowsNull()) {
				throw new InternalException('Non-nullable relation could not be found');
			}

			$property->setValue($model, $propertyModel);
		}

	}

	/**
	 * @template EntityT of object
	 * @param class-string<EntityT> $modelClass
	 */
	private function fetchManyToOneRelations(
		object $model,
		array $data,
		string $modelClass
	) {
		$manyToOneProperties = self::getManyToOneRelations($modelClass);

		foreach ($manyToOneProperties as $property) {
			$propertyType = $property->getType();

			if ($propertyType->isBuiltin()) {
				throw new InternalException('Unsupported ManyToOne relation property type');
			}

			$propertyClass = $propertyType->getName();

			$joinColumns = $property->getAttributes(JoinColumn::class);
			if (empty($joinColumns)) {
				throw new InternalException('Unsupported ManyToOne relation type');
			}

			$foreignIdColumnName = $joinColumns[0]->newInstance()->name;

			if (!array_key_exists($foreignIdColumnName, $data)) {
				throw new InternalException('JoinColumn not found');
			}

			$foreignId = $data[$foreignIdColumnName];

			$propertyModel = $this->findById($foreignId, $propertyClass);

			if ($propertyModel === null && !$propertyType->allowsNull()) {
				throw new InternalException('Non-nullable relation could not be found');
			}

			$property->setValue($model, $propertyModel);
		}
	}

	/**
	 * @template EntityT of object
	 * @param class-string<EntityT> $modelClass
	 */
	private function fetchRelations(
		object $model,
		array $data,
		string $modelClass
	) {
		self::fetchOneToOneRelations($model, $modelClass);
		self::fetchManyToOneRelations($model, $data, $modelClass);
	}

	/**
	 * @template EntityT of object
	 * @param class-string<EntityT> $modelClass
	 * @return EntityT
	 */
	private function load(array $data, string $modelClass): object
	{
		$columnProperties = self::getColumns($modelClass);

		$model = new $modelClass();

		foreach ($columnProperties as $property) {
			$column = $property->getAttributes(Column::class)[0]->newInstance();

			if (!array_key_exists($column->name, $data)) {
				throw new InternalException('Entity column not found');
			}

			$propertyClass = $property->getType()->getName();
			$value = $data[$column->name];

			if (enum_exists($propertyClass)) {
				$enumType = $propertyClass;
				$value = $enumType::from($data[$column->name]);
			} elseif ($propertyClass === DateTime::class) {
				$value = new DateTime();
				$value->setTimestamp($data[$column->name]);
			}

			$property->setValue($model, $value);
		}

		$this->fetchRelations($model, $data, $modelClass);

		return $model;
	}

	/**
	 * @template EntityT of object
	 * @param class-string<EntityT> $modelClass
	 */
	private static function dump(object $model, string $modelClass): array
	{
		$columnProperties = self::getColumns($modelClass);
		$data = [];

		foreach ($columnProperties as $property) {
			$column = $property->getAttributes(Column::class)[0]->newInstance();
			$value = $property->getValue($model);

			if ($property->getType()->getName() === DateTime::class) {
				$data[$column->name] = $value->getTimestamp();
			} else {
				$data[$column->name] = $value;
			}
		}

		$manyToOneProperties = self::getManyToOneRelations($modelClass);

		foreach ($manyToOneProperties as $property) {
			$joinColumns = $property->getAttributes(JoinColumn::class);
			if (empty($joinColumns)) {
				throw new InternalException('Unsupported ManyToOne relation type');
			}

			$column = $joinColumns[0]->newInstance();
			$foreignIdColumn = self::getIdProperty($property->getType()->getName());

			$foreignEntity = $property->getValue($model);

			$data[$column->name] = $foreignIdColumn->getValue($foreignEntity);
		}

		return $data;
	}

	/**
	 * @template EntityT of object
	 * @param array<string, string | array<string, string>> $criteria
	 * @param class-string<EntityT>
	 * @return ?EntityT
	 */
	public function findBy(array $criteria, string $modelClass): ?object
	{
		if (empty($criteria)) {
			throw new InternalException("Criteria cannot be empty");
		}

		$conditions = self::formulateConditions($criteria);

		$stmt = $this->pdo->prepare("SELECT * FROM "
			. self::getTableName($modelClass) ." WHERE " . $conditions);

		if ($stmt === false) {
			throw new InternalException('Could not prepare PDO statement');
		}

		self::bindParameters($stmt, $criteria);

		if (!$stmt->execute()) {
			throw new InternalException('Could not execute PDO statement');
		}

		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$result) {
			return null;
		}

		return $this->load($result, $modelClass);
	}

	/**
	 * @template EntityT of object
	 * @param class-string<EntityT> $modelClass
	 * @return ?EntityT
	 */
	public function findById(int|object $id, string $modelClass): ?object
	{
		$criteria = $this->getIdCriteria($id, $modelClass);

		return $this->findBy($criteria, $modelClass);
	}

	/**
	 * @template EntityT of object
	 * @param class-string<EntityT> $modelClass
	 * @return EntityT[]
	 */
	public function findAll(
		string $modelClass,
		?string $orderBy = null,
		?string $orderDirection = null
	): array {
		$query = 'SELECT * FROM ' . self::getTableName($modelClass);

		if ($orderBy !== null) {
			$query .= ' ORDER BY ' . $orderBy;

			if ($orderDirection !== null) {
				$query .= ' ' . $orderDirection;
			}
		}

		$stmt = $this->pdo->query($query);

		if ($stmt === false) {
			throw new InternalException('Could not prepare PDO statement');
		}

		if (!$stmt->execute()) {
			throw new InternalException('Could not execute PDO statement');
		}

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		return array_map(
			fn ($data) => $this->load($data, $modelClass),
			$results
		);
	}

	/**
	 * @template EntityT of object
	 * @param array<string, string | array<string, string>> $criteria
	 * @param class-string<EntityT>
	 * @return EntityT[]
	 */
	public function findAllBy(
		array $criteria,
		string $modelClass,
		?string $orderBy = null,
		?string $orderDirection = null
	): array {
		if (empty($criteria)) {
			throw new InternalException("Criteria cannot be empty");
		}

		$conditions = self::formulateConditions($criteria);

		$query = 'SELECT * FROM ' . self::getTableName($modelClass)
			. ' WHERE ' . $conditions;

		if ($orderBy !== null) {
			$query .= ' ORDER BY ' . $orderBy;

			if ($orderDirection !== null) {
				$query .= ' ' . $orderDirection;
			}
		}

		$stmt = $this->pdo->prepare($query);

		if ($stmt === false) {
			throw new InternalException('Could not prepare PDO statement');
		}

		self::bindParameters($stmt, $criteria);

		if (!$stmt->execute()) {
			throw new InternalException('Could not execute PDO statement');
		}

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		return array_map(
			fn ($data) => $this->load($data, $modelClass),
			$results
		);
	}


	/**
	 * @template EntityT of object
	 * @param array<string, string | array<string, string>> $criteria
	 * @param class-string<EntityT>
	 */
	public function countBy(array $criteria, string $modelClass): int
	{
		if (empty($criteria)) {
			throw new InternalException('Criteria cannot be empty');
		}

		$conditions = self::formulateConditions($criteria);

		$stmt = $this->pdo->prepare("SELECT COUNT(1) FROM "
			. self::getTableName($modelClass) ." WHERE " . $conditions);

		if ($stmt === false) {
			throw new InternalException('Could not prepare PDO statement');
		}

		self::bindParameters($stmt, $criteria);

		if (!$stmt->execute()) {
			throw new InternalException('Could not execute PDO statement');
		}

		$count = $stmt->fetchColumn();

		return $count;
	}

	/**
	 * @template EntityT of object
	 * @param class-string<EntityT>
	 */
	public function countAll(string $modelClass): int
	{
		$stmt = $this->pdo->prepare("SELECT COUNT(1) FROM "
			. self::getTableName($modelClass));

		if ($stmt === false) {
			throw new InternalException('Could not prepare PDO statement');
		}

		$count = $stmt->fetchColumn();

		return $count;
	}


	/**
	 * @template EntityT of object
	 * @param array<string, string | array<string, string>> $criteria
	 * @param class-string<EntityT>
	 */
	public function existsBy(array $criteria, string $modelClass): bool
	{
		if (empty($criteria)) {
			throw new InternalException('Criteria cannot be empty');
		}

		$conditions = self::formulateConditions($criteria);

		$stmt = $this->pdo->prepare('SELECT EXISTS (SELECT 1 FROM '
			. self::getTableName($modelClass) .' WHERE ' . $conditions . ')');

		if ($stmt === false) {
			throw new InternalException('Could not prepare PDO statement');
		}

		self::bindParameters($stmt, $criteria);

		if (!$stmt->execute()) {
			throw new InternalException('Could not execute PDO statement');
		}

		$exists = $stmt->fetchColumn();

		return $exists;
	}


	/**
	 * @template EntityT of object
	 * @param EntityT $model
	 * @param class-string<EntityT> $modelClass
	 */
	public function existsById(int|object $id, string $modelClass): bool
	{
		$criteria = $this->getIdCriteria($id, $modelClass);

		return $this->existsBy($criteria, $modelClass);
	}

	/**
	 * @template EntityT of object
	 * @param EntityT $model
	 * @param class-string<EntityT> $modelClass
	 * @return EntityT
	 */
	public function save(
		object $model,
		string $modelClass,
		int|object|null $id = null
	): object {
		$data = self::dump($model, $modelClass);

		$idColumnProperties = $this->getIdColumnProperties($modelClass);

		if ($idColumnProperties[0]->class === $modelClass) {
			$idColumn = $idColumnProperties[0]->getAttributes(Column::class)[0];
			$idColumnName = $idColumn->newInstance()->name;

			if ($data[$idColumnName] === 0) {
				if ($id !== null) {
					$data[$idColumnName] = $id;
				} else {
					unset($data[$idColumnName]);
				}
			}
		} else {

			$idProperty = self::getIdProperty($modelClass);
			$data = array_merge($data, self::dump($idProperty->getValue($model), $idProperty->getType()->getName()));

			foreach ($idColumnProperties as $idProperty) {
				$idColumn = $idColumnProperties[0]->getAttributes(Column::class)[0];
				$idColumnName = $idColumn->newInstance()->name;

				if ($data[$idColumnName] === 0) {
					if ($id !== null) {
						$data[$idColumnName] = $idProperty->getValue($id);
					} else {
						unset($data[$idColumnName]);
					}
				}
			}
		}

		$fields = implode(', ', array_keys($data));
		$placeholders = ':' . implode(', :', array_keys($data));

		$stmt = $this->pdo->prepare("INSERT INTO "
			. self::getTableName($modelClass)
			. " ($fields) VALUES ($placeholders)");

		if ($stmt === false) {
			throw new InternalException('Could not prepare PDO statement');
		}

		self::bindParameters($stmt, $data);

		$success = $stmt->execute();

		if (!$success) {
			throw new InternalException();
		}

		if ($idColumnProperties[0]->class === $modelClass) {
			$idProperty = $idColumnProperties[0];
			$idColumn = $idProperty->getAttributes(Column::class)[0];
			$idColumnName = $idColumn->newInstance()->name;

			if (!array_key_exists($idColumnName, $data)) {
				$insertedId = $this->pdo->lastInsertId();

				$idProperty->setValue($model, $insertedId);
			}
		}

		$idProperty = $this->getIdProperty($modelClass);
		$insertedId = $idProperty->getValue($model);

		// TODO: Use transactions to rollback if relation insertion fails

		$oneToOneProperties = self::getOneToOneRelations($modelClass);

		foreach ($oneToOneProperties as $property) {
			$this->save(
				$property->getValue($model),
				$property->getType()->getName(),
				$insertedId,
			);
		}

		return $model;
	}

	/**
	 * @template EntityT of object
	 * @param EntityT $model
	 * @param class-string<EntityT> $modelClass
	 * @return EntityT
	 */
	public function merge(object $model, string $modelClass): object
	{
		$data = self::dump($model, $modelClass);

		$idProperty = self::getIdProperty($modelClass);
		$idColumnProperties = self::getIdColumnProperties($modelClass);

		$id = $idProperty->getValue($model);

		foreach ($idColumnProperties as $idColumnProperty) {
			$idColumn = $idColumnProperty->getAttributes(Column::class)[0];
			$idColumnName = $idColumn->newInstance()->name;

			unset($data[$idColumnName]);
		}

		$criteria = $this->getIdCriteria($id, $modelClass);

		$conditions = $this->formulateConditions($criteria);

		$setClause = implode(', ', array_map(
			fn ($field) => "$field = :$field",
			array_keys($data)
		));

		$stmt = $this->pdo->prepare("UPDATE " . self::getTableName($modelClass)
			." SET $setClause WHERE " . $conditions);

		self::bindParameters($stmt, $data);
		self::bindParameters($stmt, $criteria);

		$success = $stmt->execute();

		if (!$success) {
			throw new InternalException('Could not execute PDO statement');
		}

		$oneToOneProperties = self::getOneToOneRelations($modelClass);

		foreach ($oneToOneProperties as $property) {
			$this->merge(
				$property->getValue($model),
				$property->getType()->getName(),
			);
		}

		return $model;
	}


	/**
	 * @template EntityT of object
	 * @param array<string, string | array<string, string>> $criteria
	 * @param class-string<EntityT> $modelClass
	 * @return int Number of rows affected
	 */
	public function deleteBy(array $criteria, string $modelClass): int
	{
		$conditions = self::formulateConditions($criteria);

		$stmt = $this->pdo->prepare("DELETE FROM "
			. self::getTableName($modelClass) ." WHERE " . $conditions);

		if ($stmt === false) {
			throw new InternalException('Could not prepare PDO statement');
		}

		self::bindParameters($stmt, $criteria);

		$result = $stmt->execute();

		if (!$result) {
			throw new InternalException("Could not execute PDO statement");
		}

		return $stmt->rowCount();
	}

	/**
	 * @template EntityT of object
	 * @param class-string<EntityT> $modelClass
	 * @return int Number of rows affected
	 */
	public function delete(int|object $id, string $modelClass): int
	{
		$criteria = $this->getIdCriteria($id, $modelClass);

		return $this->deleteBy($criteria, $modelClass);
	}
}
