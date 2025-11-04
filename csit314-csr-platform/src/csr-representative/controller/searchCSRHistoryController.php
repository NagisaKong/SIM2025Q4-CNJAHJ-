<?php

declare(strict_types=1);

namespace CSRPlatform\CSRRepresentative\Controller;

use CSRPlatform\Shared\Entity\Request;

final class searchCSRHistoryController
{
    public function __construct(private Request $requests)
    {
    }

    public function searchCSRHistory(
        int $csrId,
        ?string $searchQuery = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $serviceId = null
    ): array {
        return $this->requests->searchCSRHistory($csrId, $searchQuery, $startDate, $endDate, $serviceId);
    }

    public function search(int $csrId, string $query): array
    {
        return $this->searchCSRHistory($csrId, $query);
    }
}
