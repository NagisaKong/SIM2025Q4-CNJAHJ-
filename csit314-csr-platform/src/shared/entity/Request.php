<?php

declare(strict_types=1);

namespace CSRPlatform\Shared\Entity;

use CSRPlatform\Shared\Database\DatabaseConnection;

final class Request
{
    public function createRequest(
        int $pinId,
        int $categoryId,
        string $title,
        string $description,
        string $location,
        string $requestedDate
    ): bool {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('INSERT INTO pin_requests(pin_id, category_id, title, description, location, requested_date)
                VALUES (:pin_id, :category_id, :title, :description, :location, :requested_date)');
        return $stmt->execute([
            ':pin_id' => $pinId,
            ':category_id' => $categoryId,
            ':title' => trim($title),
            ':description' => trim($description),
            ':location' => trim($location),
            ':requested_date' => $requestedDate,
        ]);
    }

    public function increaseShortlistCount(int $requestId): void
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('UPDATE pin_requests SET shortlist_count = shortlist_count + 1, updated_at = NOW() WHERE id = :id');
        $stmt->execute([':id' => $requestId]);
    }

    public function updateShortlistCount(int $requestId): void
    {
        $this->increaseShortlistCount($requestId);
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

    public function searchRequests(string $searchQuery): array
    {
        return $this->searchRequestsByCriteria($searchQuery);
    }

    public function searchRequestsByCriteria(
        ?string $searchQuery = null,
        ?string $status = null,
        ?int $categoryId = null
    ): array {
        $pdo = DatabaseConnection::get();
        $conditions = [];
        $params = [];

        if ($searchQuery !== null && trim($searchQuery) !== '') {
            $conditions[] = '(pr.title ILIKE :search OR pr.description ILIKE :search OR pr.location ILIKE :search)';
            $params[':search'] = '%' . trim($searchQuery) . '%';
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
        $stmt = $pdo->prepare('SELECT pr.*, sc.name AS category_name FROM pin_requests pr
                INNER JOIN service_categories sc ON sc.id = pr.category_id
                WHERE pr.pin_id = :pin_id ORDER BY pr.created_at DESC');
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

    public function listShortlistedRequests(int $csrId): array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT pr.*, sc.name AS category_name, sl.created_at AS shortlisted_at
                FROM shortlists sl
                INNER JOIN pin_requests pr ON pr.id = sl.request_id
                INNER JOIN service_categories sc ON sc.id = pr.category_id
                WHERE sl.csr_id = :csr_id
                ORDER BY sl.created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':csr_id' => $csrId]);
        return $stmt->fetchAll();
    }

    public function getShortlistedRequest(int $requestId, int $csrId): ?array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT pr.*, sc.name AS category_name, sl.created_at AS shortlisted_at
                FROM shortlists sl
                INNER JOIN pin_requests pr ON pr.id = sl.request_id
                INNER JOIN service_categories sc ON sc.id = pr.category_id
                WHERE sl.csr_id = :csr_id AND pr.id = :request_id
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':csr_id' => $csrId,
            ':request_id' => $requestId,
        ]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function searchShortlistedRequests(int $csrId, string $searchQuery): array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT pr.*, sc.name AS category_name, sl.created_at AS shortlisted_at
                FROM shortlists sl
                INNER JOIN pin_requests pr ON pr.id = sl.request_id
                INNER JOIN service_categories sc ON sc.id = pr.category_id
                WHERE sl.csr_id = :csr_id
                AND (pr.title ILIKE :query OR pr.description ILIKE :query OR pr.location ILIKE :query)
                ORDER BY sl.created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':csr_id' => $csrId,
            ':query' => '%' . trim($searchQuery) . '%',
        ]);
        return $stmt->fetchAll();
    }

    public function getCSRHistory(int $csrId): array
    {
        return $this->searchCSRHistory($csrId);
    }

    public function searchCSRHistory(
        int $csrId,
        ?string $searchQuery = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $serviceId = null
    ): array {
        $pdo = DatabaseConnection::get();
        $conditions = ['sl.csr_id = :csr_id'];
        $params = [':csr_id' => $csrId];

        if ($searchQuery !== null && trim($searchQuery) !== '') {
            $conditions[] = '(pr.title ILIKE :history_query OR pr.description ILIKE :history_query)';
            $params[':history_query'] = '%' . trim($searchQuery) . '%';
        }

        if ($startDate !== null && $startDate !== '') {
            $conditions[] = 'sl.created_at >= :start_date';
            $params[':start_date'] = $startDate;
        }

        if ($endDate !== null && $endDate !== '') {
            $conditions[] = 'sl.created_at <= :end_date';
            $params[':end_date'] = $endDate;
        }

        if ($serviceId !== null) {
            $conditions[] = 'pr.category_id = :service_id';
            $params[':service_id'] = $serviceId;
        }

        $sql = 'SELECT pr.*, sc.name AS category_name, sl.created_at AS shortlisted_at, pr.updated_at
                FROM shortlists sl
                INNER JOIN pin_requests pr ON pr.id = sl.request_id
                INNER JOIN service_categories sc ON sc.id = pr.category_id';

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY sl.created_at DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
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
