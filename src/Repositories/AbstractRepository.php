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

	public function delete(int|object $id)
	{
		$this->entityManager->delete($id, $this->getModelClass());
	}
}
