<?php

declare(strict_types=1);

namespace CSRPlatform\Shared\Entity;

use CSRPlatform\Shared\Database\DatabaseConnection;

use DateInterval;
use DateTimeImmutable;
use PDO;

final class Report
{
    public function produceReport(string $startDate, string $endDate): array
    {
        $start = new DateTimeImmutable($startDate);
        $end = new DateTimeImmutable($endDate);
        $endExclusive = $end->add(new DateInterval('P1D'));

        $pdo = DatabaseConnection::get();

        $totalsStmt = $pdo->prepare(
            'SELECT
                COUNT(*) AS requests,
                COUNT(*) FILTER (WHERE LOWER(status) = \'completed\') AS completed
             FROM "Requests"
             WHERE "created_at" >= :start AND "created_at" < :end'
        );
        $totalsStmt->execute([
            ':start' => $start->format('Y-m-d 00:00:00'),
            ':end' => $endExclusive->format('Y-m-d 00:00:00'),
        ]);
        $totals = $totalsStmt->fetch(PDO::FETCH_ASSOC) ?: ['requests' => 0, 'completed' => 0];

        $categoriesStmt = $pdo->prepare(
            'SELECT sc."name" AS name, COUNT(r."requestID") AS total
             FROM "serviceCategories" sc
             LEFT JOIN "Requests" r
                ON r."serviceID" = sc."serviceID"
               AND r."created_at" >= :start
               AND r."created_at" < :end
             GROUP BY sc."serviceID", sc."name"
             ORDER BY sc."name" ASC'
        );
        $categoriesStmt->execute([
            ':start' => $start->format('Y-m-d 00:00:00'),
            ':end' => $endExclusive->format('Y-m-d 00:00:00'),
        ]);
        $byCategory = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'range' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
            'byCategory' => array_map(
                static fn (array $row): array => [
                    'name' => $row['name'],
                    'total' => (int) $row['total'],
                ],
                $byCategory
            ),
            'totals' => [
                'requests' => (int) ($totals['requests'] ?? 0),
                'completed' => (int) ($totals['completed'] ?? 0),
            ],
        ];
    }
}

