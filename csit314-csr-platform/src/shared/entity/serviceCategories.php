<?php
declare(strict_types=1);

namespace shared\entity;

use shared\database\DatabaseConnection;
use PDO;

class serviceCategories
{
    public function listAll(): array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT * FROM service_categories ORDER BY name ASC';
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT * FROM service_categories WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function update(int $id, string $name, string $status): bool
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('UPDATE service_categories SET name = :name, status = :status WHERE id = :id');
        return $stmt->execute([':name' => $name, ':status' => $status, ':id' => $id]);
    }

    public function create(string $name, string $status = 'active'): int
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('INSERT INTO service_categories (name, status) VALUES (:name, :status)');
        $stmt->execute([':name' => $name, ':status' => $status]);
        return (int) $pdo->lastInsertId();
    }
}
