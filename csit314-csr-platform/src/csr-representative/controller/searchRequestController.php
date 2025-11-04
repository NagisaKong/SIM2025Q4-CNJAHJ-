<?php

declare(strict_types=1);

namespace CSRPlatform\CSRRepresentative\Controller;

use CSRPlatform\Shared\Entity\Request;

final class searchRequestController
{
    public function __construct(private Request $requests)
    {
    }

    public function searchRequests(string $searchQuery, ?string $status = null, ?int $categoryId = null): array
    {
        return $this->requests->searchRequestsByCriteria($searchQuery, $status, $categoryId);
    }

    public function search(?string $query = null, ?string $status = null, ?int $categoryId = null): array
    {
        $query ??= '';
        return $this->searchRequests($query, $status, $categoryId);
    }
}
