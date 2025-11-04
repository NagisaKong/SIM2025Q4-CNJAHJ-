<?php

declare(strict_types=1);

namespace CSRPlatform\PIN\Controller;

use CSRPlatform\Shared\Entity\Request;

final class suspendRequestController
{
    public function __construct(private Request $requests)
    {
    }

    public function hideRequest(int $pinId, int $requestId, string $status): bool
    {
        return $this->requests->hideRequest($pinId, $requestId, $status);
    }

    public function suspendRequest(int $pinId, int $requestId, string $status): bool
    {
        return $this->hideRequest($pinId, $requestId, $status);
    }

    public function suspend(int $pinId, int $requestId, string $status): bool
    {
        return $this->hideRequest($pinId, $requestId, $status);
    }
}
