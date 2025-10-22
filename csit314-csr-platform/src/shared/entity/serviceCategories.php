<?php

declare(strict_types=1);

namespace CSRPlatform\Shared\Entity;

use CSRPlatform\Shared\Database\DatabaseConnection;

final class ServiceCategories
{
    public function listCategories(?string $status = null): array
    {
        $pdo = DatabaseConnection::get();
        if ($status === null || $status === 'all') {
            return $pdo->query('SELECT * FROM service_categories ORDER BY name ASC')->fetchAll();
        }
        $stmt = $pdo->prepare('SELECT * FROM service_categories WHERE status = :status ORDER BY name ASC');
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT * FROM service_categories WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function createCategory(string $name, string $status = 'active'): bool
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('INSERT INTO service_categories(name, status) VALUES (:name, :status)');
        return $stmt->execute([
            ':name' => trim($name),
            ':status' => $status,
        ]);
    }

    public function updateCategory(int $id, array $payload): bool
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
        $sql = 'UPDATE service_categories SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = :id';
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function search(string $query): array
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT * FROM service_categories WHERE name ILIKE :q ORDER BY name ASC');
        $stmt->execute([':q' => '%' . $query . '%']);
        return $stmt->fetchAll();
    }

    public function usageByCategory(): array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT sc.name, COUNT(pr.id) AS request_count
                FROM service_categories sc
                LEFT JOIN pin_requests pr ON pr.category_id = sc.id
                GROUP BY sc.id
                ORDER BY sc.name ASC';
        return $pdo->query($sql)->fetchAll();
    }
}
