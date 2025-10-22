<?php
declare(strict_types=1);

use shared\entity\Request;
use shared\utils\Validation;

class SearchCSRHistoryController
{
    private Request $requests;

    public function __construct()
    {
        $this->requests = new Request();
    }

    public function search(int $csrId, array $query): array
    {
        $term = Validation::sanitizeString($query['q'] ?? '');
        $history = $this->requests->historyForCsr($csrId);
        if ($term === '') {
            return $history;
        }

        return array_values(array_filter($history, static function (array $row) use ($term): bool {
            $needle = strtolower($term);
            return str_contains(strtolower($row['title'] ?? ''), $needle)
                || str_contains(strtolower($row['location'] ?? ''), $needle)
                || str_contains(strtolower($row['status'] ?? ''), $needle);
        }));
    }
}
