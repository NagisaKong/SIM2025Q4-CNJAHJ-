<?php

declare(strict_types=1);

namespace CSRPlatform\PIN\Controller;

use CSRPlatform\Shared\Entity\Request;

final class searchPostedRequestsController
{
    public function __construct(private Request $requests)
    {
    }

    public function searchPostedRequests(
        int $pinId,
        ?string $query = null,
        ?string $status = null,
        ?int $serviceId = null
    ): array {
        return $this->requests->searchPostedRequests($pinId, $query, $status, $serviceId);
    }

    public function search(int $pinId, ?string $query = null): array
    {
        return $this->searchPostedRequests($pinId, $query);
    }
}
