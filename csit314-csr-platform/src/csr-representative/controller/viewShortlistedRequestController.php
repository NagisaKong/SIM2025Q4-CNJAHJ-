<?php

declare(strict_types=1);

namespace CSRPlatform\CSRRepresentative\Controller;

use CSRPlatform\Shared\Entity\Request;

final class viewShortlistedRequestController
{
    public function __construct(private Request $requests)
    {
    }

    public function viewShortlistedRequest(int $requestId, int $csrId): ?array
    {
        return $this->requests->getShortlistedRequest($requestId, $csrId);
    }

    public function listShortlistedRequests(int $csrId): array
    {
        return $this->requests->listShortlistedRequests($csrId);
    }

    public function list(int $csrId): array
    {
        return $this->listShortlistedRequests($csrId);
    }
}
