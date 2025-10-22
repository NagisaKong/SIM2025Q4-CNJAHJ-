<?php
declare(strict_types=1);

namespace shared\entity;

use shared\database\DatabaseConnection;
use PDO;

class Shortlist
{
    public function add(int $csrId, int $requestId): bool
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO shortlists (csr_id, request_id) VALUES (:csr, :request)');
        return $stmt->execute([':csr' => $csrId, ':request' => $requestId]);
    }

    public function listForCsr(int $csrId): array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT s.*, pr.title, pr.location, pr.status, sc.name AS category_name
                FROM shortlists s
                JOIN pin_requests pr ON s.request_id = pr.id
                JOIN service_categories sc ON pr.category_id = sc.id
                WHERE s.csr_id = :csr ORDER BY s.created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':csr' => $csrId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchForCsr(int $csrId, string $term): array
    {
        $pdo = DatabaseConnection::get();
        $sql = 'SELECT s.*, pr.title, pr.location, pr.status, sc.name AS category_name
                FROM shortlists s
                JOIN pin_requests pr ON s.request_id = pr.id
                JOIN service_categories sc ON pr.category_id = sc.id
                WHERE s.csr_id = :csr AND (LOWER(pr.title) LIKE LOWER(:term) OR LOWER(pr.location) LIKE LOWER(:term))
                ORDER BY s.created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':csr' => $csrId, ':term' => '%' . $term . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function shortlistCount(int $requestId): int
    {
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM shortlists WHERE request_id = :request');
        $stmt->execute([':request' => $requestId]);
        return (int) $stmt->fetchColumn();
    }
}
