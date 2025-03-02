<?php

namespace App\Repositories;

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

/**
 * @template EntityT
 */
abstract class AbstractRepository
{
	protected PDO $pdo;

	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	abstract protected function getModelClass(): string;

	private function getParamType($value): int
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

	protected function getTableName(?string $modelClass = null): string
	{
		if ($modelClass === null) {
			$modelClass = $this->getModelClass();
		}

		$reflectionClass = new ReflectionClass($modelClass);

		$entityAttributes = $reflectionClass->getAttributes(Entity::class);

		if (empty($entityAttributes)) {
			throw new InternalException("Entity class must have an Entity attribute");
		}

		return $entityAttributes[0]->newInstance()->tableName;
	}

	private function getIdColumn(?string $modelClass = null): ReflectionProperty
	{
		if ($modelClass === null) {
			$modelClass = $this->getModelClass();
		}

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

	private function getIdColumnAttribute(?string $modelClass = null): Column
	{
		$idProperty = $this->getIdColumn($modelClass);

		$columnAttribute = $idProperty->getAttributes(Column::class)[0];

		return $columnAttribute->newInstance();
	}

	/** @return ReflectionProperty[] */
	private function getColumns(?string $modelClass = null): array
	{
		if ($modelClass === null) {
			$modelClass = $this->getModelClass();
		}

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
	private function getOneToOneRelations(?string $modelClass = null): array
	{
		if ($modelClass === null) {
			$modelClass = $this->getModelClass();
		}

		$reflectionClass = new ReflectionClass($modelClass);

		$columnProperties = array_filter(
			$reflectionClass->getProperties(),
			function ($property) {
				return !empty($property->getAttributes(OneToOne::class));
			}
		);

		return $columnProperties;
	}

	/** @return EntityT */
	protected function load(array $data, ?string $modelClass = null): object
	{
		if ($modelClass === null) {
			$modelClass = $this->getModelClass();
		}

		$columnProperties = $this->getColumns($modelClass);
		$idColumnProperty = $this->getIdColumn($modelClass);

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

		$oneToOneProperties = $this->getOneToOneRelations($modelClass);

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

			// $primaryKeyJoinColumn = $primaryKeyJoinColumns[0];

			$propertyIdColumn = $this->getIdColumnAttribute($propertyClass);

			$stmt = $this->pdo->prepare("SELECT * FROM {$this->getTableName($propertyClass)} WHERE " . $propertyIdColumn->name . " = :id");
			$stmt->execute(['id' => $id]);
			$result = $stmt->fetch(PDO::FETCH_ASSOC);

			// var_dump($stmt);

			if (!$result && !$propertyType->allowsNull()) {
				throw new InternalException('Non-nullable relation could not be found');
			}

			$propertyModel = $this->load($result, $propertyClass);

			$property->setValue($model, $propertyModel);
			// var_dump($propertyModel);

			// $propertyName = $property->name;
			// $model->$propertyName = $data[$column->name];
		}

		return $model;
	}

	/** @param EntityT $model */
	protected function dump(object $model, ?string $modelClass = null): array
	{
		if ($modelClass === null) {
			$modelClass = $this->getModelClass();
		}

		$columnProperties = $this->getColumns($modelClass);
		$data = [];

		foreach ($columnProperties as $property) {
			$column = $property->getAttributes(Column::class)[0]->newInstance();

			$data[$column->name] = $property->getValue($model);
		}

		return $data;
	}

	/** @return ?EntityT */
	public function findById(int $id): ?object
	{
		$stmt = $this->pdo->prepare("SELECT * FROM {$this->getTableName()} WHERE id = :id");
		$stmt->execute(['id' => $id]);
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$result) {
			return null;
		}

		return $this->load($result);
	}

	public function findAll(): array
	{
		$stmt = $this->pdo->query("SELECT * FROM {$this->getTableName()}");
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		return array_map(fn ($data) => $this->load($data), $results);
	}

	private function bindParameters(PDOStatement $stmt, array $data)
	{
		foreach ($data as $key => $value) {
			if ($value instanceof BackedEnum) {
				$type = PDO::PARAM_STR;
				$value = $value->value;
			} else {
				$type = $this->getParamType($value);
			}
			$stmt->bindValue(":$key", $value, $type);
		}
	}

	private function saveInternal(object $model, ?string $modelClass = null, ?int $id = null): int
	{
		if ($modelClass === null) {
			$modelClass = $this->getModelClass();
		}

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

		$stmt = $this->pdo->prepare("INSERT INTO {$this->getTableName($modelClass)} ($fields) VALUES ($placeholders)");

		if ($stmt === false) {
			throw new InternalException();
		}

		$this->bindParameters($stmt, $data);

		$success = $stmt->execute();

		if (!$success) {
			throw new InternalException();
		}

		$insertedId = (int)$this->pdo->lastInsertId();

		$oneToOneProperties = $this->getOneToOneRelations($modelClass);

		// TODO: Use transactions to rollback if relation insertion fails

		foreach ($oneToOneProperties as $property) {
			$this->saveInternal(
				$property->getValue($model),
				$property->getType()->getName(),
				$insertedId,
			);
		}

		return $insertedId;
	}

	public function save($model): int
	{
		return $this->saveInternal($model);
	}

	public function update($model): bool
	{
		$data = $this->dump($model);

		$idColumnProperty = $this->getIdColumn();
		$idColumn = $idColumnProperty->getAttributes(Column::class)[0]->newInstance();

		$id = $idColumnProperty->getValue($model);
		unset($data[$idColumn->name]);

		$setClause = implode(', ', array_map(fn ($field) => "$field = :$field", array_keys($data)));

		$stmt = $this->pdo->prepare("UPDATE {$this->getTableName()} SET $setClause WHERE " . $idColumn->name . " = :id");

		$this->bindParameters($stmt, $data);
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);

		return $stmt->execute();
	}

	public function delete(int $id): bool
	{
		$stmt = $this->pdo->prepare("DELETE FROM {$this->getTableName()} WHERE id = :id");
		return $stmt->execute(['id' => $id]);
	}
}
