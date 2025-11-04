<?php

declare(strict_types=1);

namespace CSRPlatform\PIN\Controller;

use CSRPlatform\Shared\Entity\Request;

final class viewRequestViewCountController
{
    public function __construct(private Request $requests)
    {
    }

    public function viewRequestViewCount(int $requestId): int
    {
        return $this->requests->getRequestViewCount($requestId);
    }
}
