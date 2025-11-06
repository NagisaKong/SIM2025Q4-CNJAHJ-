<?php

declare(strict_types=1);

namespace CSRPlatform\CSRRepresentative\Controller;

use CSRPlatform\Shared\Entity\Request;

final class viewCSRHistoryController
{
    public function __construct(private Request $requests)
    {
    }

    public function viewHistory(int $csrId): array
    {
        return $this->requests->getCSRHistory($csrId);
    }

    public function history(int $csrId): array
    {
        return $this->viewHistory($csrId);
    }
}
