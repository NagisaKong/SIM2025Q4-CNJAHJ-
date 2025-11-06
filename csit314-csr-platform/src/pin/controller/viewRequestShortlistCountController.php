<?php

declare(strict_types=1);

namespace CSRPlatform\PIN\Controller;

use CSRPlatform\Shared\Entity\Request;

final class viewRequestShortlistCountController
{
    public function __construct(private Request $requests)
    {
    }

    public function viewRequestShortlistCount(int $requestId): int
    {
        return $this->requests->getRequestShortlistCount($requestId);
    }

    public function list(int $pinId): array
    {
        return $this->requests->shortlistCountsForPin($pinId);
    }
}
