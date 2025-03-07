<?php

namespace App\Repositories;

use App\EntityManager;

/**
 * @template EntityT
 */
abstract class AbstractRepository
{
	protected function __construct(protected readonly EntityManager $entityManager)
	{
	}

	abstract protected function getModelClass(): string;

	/** @return ?EntityT $model */
	protected function findBy(array $criteria): ?object
	{
		return $this->entityManager->findBy($criteria, $this->getModelClass());
	}

	/** @return ?EntityT $model */
	public function findById(int $id): ?object
	{
		return $this->entityManager->findById($id, $this->getModelClass());
	}

	/** @return EntityT[] $model */
	public function findAll(): array
	{
		return $this->entityManager->findAll($this->getModelClass());
	}

	/** @param EntityT $model */
	public function save($model): int
	{
		return $this->entityManager->save($model, $this->getModelClass());
	}

	/** @param EntityT $model */
	public function update($model): bool
	{
		return $this->entityManager->merge($model, $this->getModelClass());
	}

	public function delete(int $id): bool
	{
		return $this->entityManager->delete($id, $this->getModelClass());
	}
}
