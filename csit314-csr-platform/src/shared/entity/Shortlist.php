<?php

declare(strict_types=1);

namespace CSRPlatform\Shared\Entity;

use CSRPlatform\Shared\Database\DatabaseConnection;
use PDOException;

final class Shortlist
{
    public function addToShortlist(int $csrId, int $requestId): bool
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('INSERT INTO shortlists(csr_id, request_id) VALUES (:csr_id, :request_id) ON CONFLICT DO NOTHING');
        return $stmt->execute([':csr_id' => $csrId, ':request_id' => $requestId]);
    }

    public function removeFromShortlist(int $csrId, int $requestId): bool
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('DELETE FROM shortlists WHERE csr_id = :csr_id AND request_id = :request_id');
        return $stmt->execute([':csr_id' => $csrId, ':request_id' => $requestId]);
    }

    public function shortlistedRequests(int $csrId): array
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

    public function searchShortlist(int $csrId, string $query): array
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
        $stmt->execute([':csr_id' => $csrId, ':query' => '%' . $query . '%']);
        return $stmt->fetchAll();
    }

    public function csrHistory(int $csrId): array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT pr.title, pr.status, sl.created_at, pr.updated_at
                FROM shortlists sl
                INNER JOIN pin_requests pr ON pr.id = sl.request_id
                WHERE sl.csr_id = :csr_id
                ORDER BY sl.created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':csr_id' => $csrId]);
        return $stmt->fetchAll();
    }
}
