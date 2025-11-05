<?php

declare(strict_types=1);

namespace CSRPlatform\Shared\Entity;

use CSRPlatform\Shared\Database\DatabaseConnection;

use PDO;

final class ServiceCategories
{
    private ?int $lastInsertedId = null;

    public function listCategories(?string $status = null): array
    {
        $pdo = DatabaseConnection::get();
        if ($status === null || $status === '' || strtolower($status) === 'all') {
            return $pdo->query('SELECT * FROM service_categories ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt = $pdo->prepare('SELECT * FROM service_categories WHERE status = :status ORDER BY name ASC');
        $stmt->execute([':status' => strtolower($status)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search(string $query): array
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT * FROM service_categories WHERE name ILIKE :q ORDER BY name ASC');
        $stmt->execute([':q' => '%' . trim($query) . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getServiceCategory(string $serviceID): ?array
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT * FROM service_categories WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => (int) $serviceID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function find(int $id): ?array
    {
        return $this->getServiceCategory((string) $id);
    }

    public function createCategory(string $name, string $description = '', string $status = 'active'): bool
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('INSERT INTO "serviceCategories"("name", "description", "status") VALUES (:name, :description, :status) RETURNING "serviceID"');
        $stmt->execute([
            ':name' => trim($name),
            ':description' => trim($description),
            ':status' => strtolower($status),
        ]);
        $this->lastInsertedId = (int) $stmt->fetchColumn();

        return $this->lastInsertedId > 0;
    }

    public function updateServiceCategory(string $serviceID, string $name, string $description, string $status): bool
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('UPDATE "serviceCategories"
            SET "name" = :name,
                "description" = :description,
                "status" = :status,
                "updated_at" = NOW()
            WHERE "serviceID" = :id');

        return $stmt->execute([
            ':id' => (int) $serviceID,
            ':name' => trim($name),
            ':description' => trim($description),
            ':status' => strtolower(trim($status)),
        ]);
    }

    public function hideServiceCategory(string $serviceID, string $status): bool
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('UPDATE "serviceCategories"
            SET "status" = :status,
                "updated_at" = NOW()
            WHERE "serviceID" = :id');

        return $stmt->execute([
            ':id' => (int) $serviceID,
            ':status' => strtolower(trim($status)),
        ]);
    }

    public function updateCategory(int $id, array $payload): bool
    {
        $name = $payload['name'] ?? null;
        $description = $payload['description'] ?? '';
        $status = $payload['status'] ?? null;

        if ($name !== null && $status !== null) {
            return $this->updateServiceCategory((string) $id, (string) $name, (string) $description, (string) $status);
        }

        if ($status !== null) {
            return $this->hideServiceCategory((string) $id, (string) $status);
        }

        return false;
    }

    public function usageByCategory(): array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT sc."name" AS name, COUNT(r."requestID") AS request_count
                FROM "serviceCategories" sc
                LEFT JOIN "Requests" r
                    ON r."serviceID" = sc."serviceID"
                GROUP BY sc."serviceID", sc."name"
                ORDER BY sc."name" ASC';
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLastInsertedId(): ?int
    {
        return $this->lastInsertedId;
    }
}

