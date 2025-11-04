<?php

declare(strict_types=1);

namespace CSRPlatform\CSRRepresentative\Controller;

use CSRPlatform\Shared\Entity\Request;

final class searchShortlistController
{
    public function __construct(private Request $requests)
    {
    }

    public function searchShortlists(int $csrId, string $searchQuery): array
    {
        return $this->requests->searchShortlistedRequests($csrId, $searchQuery);
    }

    public function search(int $csrId, string $query): array
    {
        return $this->searchShortlists($csrId, $query);
    }
}
