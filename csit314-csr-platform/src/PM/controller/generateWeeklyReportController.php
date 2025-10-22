<?php
declare(strict_types=1);

use shared\database\DatabaseConnection;
use shared\utils\Validation;

class GenerateWeeklyReportController
{
    public function generate(array $input): array
    {
        $weekStart = Validation::sanitizeString($input['week_start'] ?? date('Y-m-d', strtotime('monday this week')));
        $weekEnd = Validation::sanitizeString($input['week_end'] ?? date('Y-m-d', strtotime('sunday this week')));
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT status, COUNT(*) AS total FROM pin_requests
            WHERE requested_date BETWEEN :start AND :end GROUP BY status');
        $stmt->execute([':start' => $weekStart, ':end' => $weekEnd]);
        $rows = $stmt->fetchAll();
        $summary = ['open' => 0, 'matched' => 0, 'completed' => 0];
        foreach ($rows as $row) {
            $status = strtolower((string) $row['status']);
            $summary[$status] = (int) $row['total'];
        }

        return [
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'open_requests' => $summary['open'] ?? 0,
            'matched_requests' => $summary['matched'] ?? 0,
            'completed_requests' => $summary['completed'] ?? 0,
        ];
    }
}
