<?php
declare(strict_types=1);

use shared\entity\Request;
use shared\utils\Validation;

class SearchPostedRequestsController
{
    private Request $requests;

    public function __construct()
    {
        $this->requests = new Request();
    }

    public function search(int $pinId, array $query): array
    {
        $list = $this->requests->listByPin($pinId);
        $term = Validation::sanitizeString($query['q'] ?? '');
        if ($term === '') {
            return $list;
        }

        $needle = strtolower($term);
        return array_values(array_filter($list, static function (array $row) use ($needle): bool {
            return str_contains(strtolower($row['title'] ?? ''), $needle)
                || str_contains(strtolower($row['location'] ?? ''), $needle)
                || str_contains(strtolower($row['status'] ?? ''), $needle);
        }));
    }
}
