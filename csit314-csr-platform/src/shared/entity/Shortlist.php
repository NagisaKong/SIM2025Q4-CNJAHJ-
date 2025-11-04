<?php

declare(strict_types=1);

namespace CSRPlatform\Shared\Entity;

use CSRPlatform\Shared\Database\DatabaseConnection;

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
        return (new Request())->listShortlistedRequests($csrId);
    }

    public function searchShortlist(int $csrId, string $query): array
    {
        return (new Request())->searchShortlistedRequests($csrId, $query);
    }

    public function csrHistory(int $csrId): array
    {
        return (new Request())->searchCSRHistory($csrId);
    }
}
