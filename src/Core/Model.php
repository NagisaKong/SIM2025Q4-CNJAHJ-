<?php

namespace App\Core;

use PDO;

abstract class Model
{
    protected static string $table;

    public function __construct(protected PDO $pdo)
    {
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM ' . static::$table . ' WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function all(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM ' . static::$table . ' LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
