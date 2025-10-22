<?php

declare(strict_types=1);

namespace CSRPlatform\PM\Controller;

use CSRPlatform\Shared\Database\DatabaseConnection;

final class generateWeeklyReportController
{
    public function generate(): array
    {
        $pdo = DatabaseConnection::get();
        $summarySql = "SELECT status, COUNT(*) AS total FROM pin_requests WHERE created_at >= CURRENT_DATE - INTERVAL '6 days' GROUP BY status";
        $summary = $pdo->query($summarySql)->fetchAll();

        $trendSql = "SELECT DATE(created_at) AS date, COUNT(*) AS total
                     FROM pin_requests
                     WHERE created_at >= CURRENT_DATE - INTERVAL '6 days'
                     GROUP BY DATE(created_at)
                     ORDER BY DATE(created_at)";
        $trend = $pdo->query($trendSql)->fetchAll();

        $categorySql = "SELECT sc.name, COUNT(pr.id) AS total
                        FROM service_categories sc
                        LEFT JOIN pin_requests pr ON pr.category_id = sc.id AND pr.created_at >= CURRENT_DATE - INTERVAL '6 days'
                        GROUP BY sc.id
                        ORDER BY sc.name";
        $byCategory = $pdo->query($categorySql)->fetchAll();

        return [
            'summary' => $summary,
            'trend' => $trend,
            'categories' => $byCategory,
        ];
    }
}
