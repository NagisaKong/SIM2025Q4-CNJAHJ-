<?php

namespace App\Repositories;

use App\Models\Shortlist;
use PDO;

class ShortlistRepository extends Repository
{
    public function findByCsrAndRequest(int $csrId, int $requestId): ?Shortlist
    {
        $stmt = $this->pdo->prepare('SELECT * FROM shortlists WHERE csr_id = :csr_id AND request_id = :request_id');
        $stmt->execute(['csr_id' => $csrId, 'request_id' => $requestId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? Shortlist::fromArray($row) : null;
    }

    public function add(int $csrId, int $requestId): Shortlist
    {
        $existing = $this->findByCsrAndRequest($csrId, $requestId);
        if ($existing) {
            return $existing;
        }
        $stmt = $this->pdo->prepare('INSERT INTO shortlists (csr_id, request_id, created_at) VALUES (:csr_id, :request_id, :created_at)');
        $stmt->execute([
            'csr_id' => $csrId,
            'request_id' => $requestId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return new Shortlist((int) $this->pdo->lastInsertId(), $csrId, $requestId, date('Y-m-d H:i:s'));
    }

    public function listForCsr(int $csrId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM shortlists WHERE csr_id = :csr_id ORDER BY created_at DESC');
        $stmt->execute(['csr_id' => $csrId]);
        return array_map(fn($row) => Shortlist::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
