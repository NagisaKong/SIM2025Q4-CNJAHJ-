<?php
declare(strict_types=1);

use shared\entity\Request;
use shared\utils\Validation;

class SearchRequestController
{
    private Request $requests;

    public function __construct()
    {
        $this->requests = new Request();
    }

    public function search(array $query): array
    {
        $term = Validation::sanitizeString($query['q'] ?? '');
        $categoryId = $query['category'] ?? null;
        $categoryId = $categoryId ? (int) $categoryId : null;
        return $this->requests->searchOpenRequests($term ?: null, $categoryId);
    }
}
