<?php

declare(strict_types=1);

namespace CSRPlatform\PIN\Controller;

use CSRPlatform\Shared\Entity\Request;

final class viewRequestController
{
    public function __construct(private Request $requests)
    {
    }

    public function view(int $requestId, bool $incrementView = true): ?array
    {
        if ($incrementView) {
            $this->requests->incrementView($requestId);
        }
        return $this->requests->find($requestId);
    }
}
