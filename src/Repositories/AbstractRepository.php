<?php

namespace App\Repositories;

use App\Exceptions\InternalException;
use PDO;

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

    abstract protected function getTableName(): string;
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

    /** @return ?EntityT */
    public function findById(int $id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->getTableName()} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return null;
        }

        $modelClass = $this->getModelClass();
        return $modelClass::load($result);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->getTableName()}");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $modelClass = $this->getModelClass();
        return array_map(fn ($data) => $modelClass::load($data), $results);
    }

    public function save($model): int
    {
        $data = $model->toArray();
        if ($data['id'] == 0) {
            unset($data['id']);
        }

        $fields = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $stmt = $this->pdo->prepare("INSERT INTO {$this->getTableName()} ($fields) VALUES ($placeholders)");

        foreach ($data as $key => $value) {
            $type = $this->getParamType($value);
            $stmt->bindValue(":$key", $value, $type);
        }

        $success = $stmt->execute();

        if (!$success) {
            throw new InternalException();
        }

        $id = (int)$this->pdo->lastInsertId();

        return $id;
    }

    public function update($model): bool
    {
        $data = $model->toArray();
        $id = $data['id'];
        unset($data['id']);

        $setClause = implode(', ', array_map(fn ($field) => "$field = :$field", array_keys($data)));

        $stmt = $this->pdo->prepare("UPDATE {$this->getTableName()} SET $setClause WHERE id = :id");

        foreach ($data as $key => $value) {
            $type = $this->getParamType($value);
            $stmt->bindValue(":$key", $value, $type);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->getTableName()} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
