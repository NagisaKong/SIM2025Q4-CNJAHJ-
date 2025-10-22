<?php
declare(strict_types=1);

namespace shared\entity;

use shared\database\DatabaseConnection;
use PDO;

class UserProfiles
{
    public function listProfiles(): array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT * FROM profiles ORDER BY id DESC';
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findProfile(int $id): ?array
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT * FROM profiles WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createProfile(string $role, string $description): int
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('INSERT INTO profiles (role, description, status) VALUES (:role, :description, :status)');
        $stmt->execute([
            ':role' => $role,
            ':description' => $description,
            ':status' => 'active',
        ]);
        return (int) $pdo->lastInsertId();
    }

    public function suspendProfile(int $id): bool
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('UPDATE profiles SET status = :status WHERE id = :id');
        return $stmt->execute([':status' => 'suspended', ':id' => $id]);
    }
}
