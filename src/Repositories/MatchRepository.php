<?php

namespace App\Repositories;

use App\Models\MatchRecord;
use PDO;

class MatchRepository extends Repository
{
    public function listForCsr(int $csrId, array $filters = []): array
    {
        $sql = 'SELECT * FROM matches WHERE csr_id = :csr_id';
        $params = ['csr_id' => $csrId];
        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
        }
        if (!empty($filters['from'])) {
            $sql .= ' AND matched_at >= :from';
        }
        if (!empty($filters['to'])) {
            $sql .= ' AND matched_at <= :to';
        }
        $sql .= ' ORDER BY matched_at DESC';
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':'.$key, $value);
        }
        if (!empty($filters['status'])) {
            $stmt->bindValue(':status', $filters['status']);
        }
        if (!empty($filters['from'])) {
            $stmt->bindValue(':from', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $stmt->bindValue(':to', $filters['to']);
        }
        $stmt->execute();
        return array_map(fn($row) => MatchRecord::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function listForPin(int $pinId, array $filters = []): array
    {
        $sql = 'SELECT m.* FROM matches m JOIN pin_requests r ON r.id = m.request_id WHERE r.pin_id = :pin_id';
        $params = ['pin_id' => $pinId];
        if (!empty($filters['status'])) {
            $sql .= ' AND m.status = :status';
        }
        if (!empty($filters['from'])) {
            $sql .= ' AND m.matched_at >= :from';
        }
        if (!empty($filters['to'])) {
            $sql .= ' AND m.matched_at <= :to';
        }
        $sql .= ' ORDER BY m.matched_at DESC';
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':'.$key, $value);
        }
        if (!empty($filters['status'])) {
            $stmt->bindValue(':status', $filters['status']);
        }
        if (!empty($filters['from'])) {
            $stmt->bindValue(':from', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $stmt->bindValue(':to', $filters['to']);
        }
        $stmt->execute();
        return array_map(fn($row) => MatchRecord::fromArray($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function aggregateByPeriod(string $period): array
    {
        $stmt = $this->pdo->query('SELECT matched_at, completed_at FROM matches');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $groups = [];

        foreach ($rows as $row) {
            $matchedAt = new \DateTimeImmutable($row['matched_at']);
            $completedAt = $row['completed_at'] ? new \DateTimeImmutable($row['completed_at']) : null;

            $key = match ($period) {
                'weekly' => $matchedAt->format('o-\WW'),
                'monthly' => $matchedAt->format('Y-m'),
                default => $matchedAt->format('Y-m-d'),
            };

            if (!isset($groups[$key])) {
                $groups[$key] = ['period' => $key, 'matches_created' => 0, 'matches_completed' => 0];
            }

            $groups[$key]['matches_created']++;
            if ($completedAt) {
                $groups[$key]['matches_completed']++;
            }
        }

        krsort($groups);
        return array_values($groups);
    }
}
