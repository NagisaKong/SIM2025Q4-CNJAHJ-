<?php

declare(strict_types=1);

namespace CSRPlatform\PIN\Controller;

use CSRPlatform\Shared\Entity\Request;

final class searchPINHistoryController
{
    public function __construct(private Request $requests)
    {
    }

    public function searchPINHistory(
        int $pinId,
        ?string $query = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $serviceId = null
    ): array {
        return $this->requests->searchPINHistory($pinId, $query, $startDate, $endDate, $serviceId);
    }
}
