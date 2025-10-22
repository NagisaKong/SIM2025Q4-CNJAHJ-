<?php

declare(strict_types=1);

namespace CSRPlatform\PIN\Controller;

use CSRPlatform\Shared\Entity\Request;

final class viewPINHistoryController
{
    public function __construct(private Request $requests)
    {
    }

    public function history(int $pinId): array
    {
        return $this->requests->requestHistory($pinId);
    }
}
