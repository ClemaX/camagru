<?php

namespace App\Repositories;

use App\EntityManager;

/**
 * @template EntityT of object
 */
abstract class AbstractRepository
{
	protected function __construct(
		protected readonly EntityManager $entityManager
	) {
	}

	/** @return class-string<EntityT> */
	abstract protected function getModelClass(): string;

	/**
	 * @param array<string, string | array<string, string>> $criteria
	 * @return ?EntityT $model
	 */
	protected function findBy(array $criteria): ?object
	{
		return $this->entityManager->findBy($criteria, $this->getModelClass());
	}

	/** @return ?EntityT $model */
	public function findById(int | object $id): ?object
	{
		return $this->entityManager->findById($id, $this->getModelClass());
	}

	/** @return EntityT[] $model */
	public function findAll(
		?string $orderBy = null,
		?string $orderDirection = null
	): array {
		return $this->entityManager->findAll(
			$this->getModelClass(),
			$orderBy,
			$orderDirection
		);
	}

	/**
	 * @template EntityT of object
	 * @param array<string, string | array<string, string>> $criteria
	 */
	protected function countBy(array $criteria): int
	{
		return $this->entityManager->countBy($criteria, $this->getModelClass());
	}

	protected function existsBy(array $criteria): int
	{
		return $this->entityManager->existsBy($criteria, $this->getModelClass());
	}

	public function existsById(int | object $id): bool
	{
		return $this->entityManager->existsById($id, $this->getModelClass());
	}

	/**
	 * @param EntityT $model
	 * @return EntityT
	 */
	public function save(object $model): object
	{
		return $this->entityManager->save($model, $this->getModelClass());
	}

	/**
	 * @param EntityT $model
	 * @return EntityT
	 */
	public function update(object $model): object
	{
		return $this->entityManager->merge($model, $this->getModelClass());
	}

	/**
	 * @param array<string, string | array<string, string>> $criteria
	 */
	protected function deleteBy(array $criteria)
	{
		return $this->entityManager->deleteBy(
			$criteria,
			$this->getModelClass()
		);
	}

	public function delete(int|object $id): int
	{
		return $this->entityManager->delete($id, $this->getModelClass());
	}
}
