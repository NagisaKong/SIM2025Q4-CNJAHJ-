<?php
declare(strict_types=1);

use shared\database\DatabaseConnection;
use shared\utils\Validation;

class GenerateDailyReportController
{
    public function generate(array $input): array
    {
        $date = Validation::sanitizeString($input['date'] ?? date('Y-m-d'));
        $pdo = DatabaseConnection::get();
        $stmt = $pdo->prepare('SELECT COUNT(*) AS total, SUM(CASE WHEN status = "open" THEN 1 ELSE 0 END) AS open_count,
            SUM(CASE WHEN status = "matched" THEN 1 ELSE 0 END) AS matched_count
            FROM pin_requests WHERE requested_date = :date');
        $stmt->execute([':date' => $date]);
        $data = $stmt->fetch();
        return [
            'date' => $date,
            'total_requests' => (int) ($data['total'] ?? 0),
            'open_requests' => (int) ($data['open_count'] ?? 0),
            'matched_requests' => (int) ($data['matched_count'] ?? 0),
        ];
    }
}
