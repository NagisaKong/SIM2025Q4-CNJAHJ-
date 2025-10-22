<?php
declare(strict_types=1);

namespace shared\entity;

use shared\database\DatabaseConnection;
use PDO;

class Request
{
    public function create(array $data): int
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('INSERT INTO pin_requests (pin_id, category_id, title, description, location, status, requested_date, views_count, shortlist_count)
            VALUES (:pin_id, :category_id, :title, :description, :location, :status, :requested_date, 0, 0)');
        $stmt->execute([
            ':pin_id' => $data['pin_id'],
            ':category_id' => $data['category_id'],
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':location' => $data['location'],
            ':status' => $data['status'] ?? 'open',
            ':requested_date' => $data['requested_date'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    public function listByPin(int $pinId): array
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT pr.*, sc.name AS category_name FROM pin_requests pr JOIN service_categories sc ON pr.category_id = sc.id WHERE pr.pin_id = :pin ORDER BY pr.id DESC');
        $stmt->execute([':pin' => $pinId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $requestId): ?array
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT pr.*, sc.name AS category_name, u.name AS pin_name FROM pin_requests pr
            JOIN service_categories sc ON pr.category_id = sc.id
            JOIN users u ON pr.pin_id = u.id WHERE pr.id = :id');
        $stmt->execute([':id' => $requestId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function incrementViewCount(int $requestId): void
    {
        $pdo = DatabaseConnection::get();
        $pdo->prepare('UPDATE pin_requests SET views_count = views_count + 1 WHERE id = :id')->execute([':id' => $requestId]);
    }

    public function searchOpenRequests(?string $term = null, ?int $categoryId = null): array
    {
        $pdo = DatabaseConnection::get();
        $conditions = ["pr.status = 'open'"];
        $params = [];
        if ($term) {
            $conditions[] = '(LOWER(pr.title) LIKE LOWER(:term) OR LOWER(pr.description) LIKE LOWER(:term))';
            $params[':term'] = '%' . $term . '%';
        }
        if ($categoryId) {
            $conditions[] = 'pr.category_id = :category';
            $params[':category'] = $categoryId;
        }
        $where = implode(' AND ', $conditions);
        $sql = 'SELECT pr.*, sc.name AS category_name, u.name AS pin_name FROM pin_requests pr
                JOIN service_categories sc ON pr.category_id = sc.id
                JOIN users u ON pr.pin_id = u.id
                WHERE ' . $where . ' ORDER BY pr.requested_date DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateShortlistCount(int $requestId, int $delta = 1): void
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('UPDATE pin_requests SET shortlist_count = shortlist_count + :delta WHERE id = :id');
        $stmt->execute([':delta' => $delta, ':id' => $requestId]);
    }

    public function historyForPin(int $pinId): array
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT pr.*, m.status AS match_status, m.matched_at, m.completed_at FROM pin_requests pr
            LEFT JOIN matches m ON m.request_id = pr.id
            WHERE pr.pin_id = :pin AND pr.status != "open" ORDER BY pr.requested_date DESC');
        $stmt->execute([':pin' => $pinId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function historyForCsr(int $csrId): array
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT pr.*, m.status AS match_status, m.matched_at, m.completed_at FROM matches m
            JOIN pin_requests pr ON m.request_id = pr.id WHERE m.csr_id = :csr ORDER BY m.matched_at DESC');
        $stmt->execute([':csr' => $csrId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
