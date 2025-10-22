<?php

declare(strict_types=1);

namespace CSRPlatform\Shared\Entity;

use CSRPlatform\Shared\Database\DatabaseConnection;

final class UserProfiles
{
    public function listProfiles(?string $status = null): array
    {
        $pdo = DatabaseConnection::get();
        if ($status === null || $status === 'all') {
            $sql = 'SELECT * FROM profiles ORDER BY id DESC';
            return $pdo->query($sql)->fetchAll();
        }

        $stmt = $pdo->prepare('SELECT * FROM profiles WHERE status = :status ORDER BY id DESC');
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll();
    }

    public function findByRole(string $role): ?array
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT * FROM profiles WHERE role = :role LIMIT 1');
        $stmt->execute([':role' => $role]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function findById(int $id): ?array
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT * FROM profiles WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function createProfile(string $role, string $description, string $status = 'active'): bool
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('INSERT INTO profiles(role, description, status) VALUES (:role, :description, :status)');
        return $stmt->execute([
            ':role' => strtolower(trim($role)),
            ':description' => trim($description),
            ':status' => $status,
        ]);
    }

    public function updateProfile(int $id, array $payload): bool
    {
        if ($payload === []) {
            return false;
        }
        $fields = [];
        $params = [':id' => $id];
        foreach ($payload as $key => $value) {
            $fields[] = sprintf('%s = :%s', $key, $key);
            $params[':' . $key] = $value;
        }
        $sql = 'UPDATE profiles SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id';
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function search(string $query): array
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare("SELECT * FROM profiles WHERE role ILIKE :q OR description ILIKE :q ORDER BY id DESC");
        $stmt->execute([':q' => '%' . $query . '%']);
        return $stmt->fetchAll();
    }
}
