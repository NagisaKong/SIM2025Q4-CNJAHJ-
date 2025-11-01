<?php

declare(strict_types=1);

namespace CSRPlatform\Shared\Entity;

use CSRPlatform\Shared\Database\DatabaseConnection;

final class Request
{
    public function createRequest(int $pinId, int $categoryId, string $title, string $description, string $location, string $requestedDate): bool
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('INSERT INTO pin_requests(pin_id, category_id, title, description, location, requested_date) VALUES (:pin_id, :category_id, :title, :description, :location, :requested_date)');
        return $stmt->execute([
            ':pin_id' => $pinId,
            ':category_id' => $categoryId,
            ':title' => trim($title),
            ':description' => trim($description),
            ':location' => trim($location),
            ':requested_date' => $requestedDate,
        ]);
    }

    public function updateShortlistCount(int $requestId): void
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('UPDATE pin_requests SET shortlist_count = (SELECT COUNT(*) FROM shortlists WHERE request_id = :id) WHERE id = :id');
        $stmt->execute([':id' => $requestId]);
    }

    public function incrementView(int $requestId): void
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('UPDATE pin_requests SET views_count = views_count + 1 WHERE id = :id');
        $stmt->execute([':id' => $requestId]);
    }

    public function find(int $id): ?array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT pr.*, sc.name AS category_name, u.name AS pin_name FROM pin_requests pr
                INNER JOIN service_categories sc ON sc.id = pr.category_id
                INNER JOIN users u ON u.id = pr.pin_id
                WHERE pr.id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function searchRequests(?string $search = null, ?string $status = null, ?int $categoryId = null): array
    {
        $pdo = DatabaseConnection::get();
        $conditions = [];
        $params = [];

        if ($search !== null && trim($search) !== '') {
            $conditions[] = '(pr.title ILIKE :search OR pr.description ILIKE :search OR pr.location ILIKE :search)';
            $params[':search'] = '%' . trim($search) . '%';
        }

        if ($status !== null && $status !== '' && strtolower($status) !== 'all') {
            $conditions[] = 'pr.status = :status';
            $params[':status'] = strtolower($status);
        }

        if ($categoryId !== null) {
            $conditions[] = 'pr.category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }

        $sql = 'SELECT pr.*, sc.name AS category_name, u.name AS pin_name
                FROM pin_requests pr
                INNER JOIN service_categories sc ON sc.id = pr.category_id
                INNER JOIN users u ON u.id = pr.pin_id';
        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY pr.created_at DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function listRequestsByPin(int $pinId): array
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT pr.*, sc.name AS category_name FROM pin_requests pr INNER JOIN service_categories sc ON sc.id = pr.category_id WHERE pr.pin_id = :pin_id ORDER BY pr.created_at DESC');
        $stmt->execute([':pin_id' => $pinId]);
        return $stmt->fetchAll();
    }

    public function shortlistCountsForPin(int $pinId): array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT pr.id, pr.title, pr.shortlist_count FROM pin_requests pr WHERE pr.pin_id = :pin_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':pin_id' => $pinId]);
        return $stmt->fetchAll();
    }

    public function requestHistory(int $pinId): array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT pr.*, sc.name AS category_name, pr.updated_at FROM pin_requests pr
                INNER JOIN service_categories sc ON sc.id = pr.category_id
                WHERE pr.pin_id = :pin_id ORDER BY pr.updated_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':pin_id' => $pinId]);
        return $stmt->fetchAll();
    }
}
