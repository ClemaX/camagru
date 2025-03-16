<?php

namespace App\Repositories;

use App\Entities\User;
use App\EntityManager;
use PDO;

/** @extends AbstractRepository<User> */
class UserRepository extends AbstractRepository
{
	public function __construct(EntityManager $entityManager)
	{
		parent::__construct($entityManager);
	}

	protected function getModelClass(): string
	{
		return User::class;
	}

	public function findByUsername(string $username): ?User
	{
		return $this->findBy(['username' => $username]);
	}

	public function findByEmailAddress(string $emailAddress): ?User
	{
		return $this->findBy(['email_address' => $emailAddress]);
	}
}
