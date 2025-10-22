<?php

declare(strict_types=1);

namespace CSRPlatform\PM\Controller;

use CSRPlatform\Shared\Database\DatabaseConnection;

final class generateDailyReportController
{
    public function generate(): array
    {
        $pdo = DatabaseConnection::get();
        $summarySql = "SELECT status, COUNT(*) AS total FROM pin_requests WHERE DATE(created_at) = CURRENT_DATE GROUP BY status";
        $summary = $pdo->query($summarySql)->fetchAll();

        $categorySql = "SELECT sc.name, COUNT(pr.id) AS total
                        FROM service_categories sc
                        LEFT JOIN pin_requests pr ON pr.category_id = sc.id AND DATE(pr.created_at) = CURRENT_DATE
                        GROUP BY sc.id
                        ORDER BY sc.name";
        $byCategory = $pdo->query($categorySql)->fetchAll();

        return [
            'summary' => $summary,
            'categories' => $byCategory,
        ];
    }
}
