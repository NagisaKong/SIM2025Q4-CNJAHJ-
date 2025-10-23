<?php

declare(strict_types=1);

namespace CSRPlatform\CSRRepresentative\Controller;

use CSRPlatform\Shared\Entity\Shortlist;

final class viewCSRHistoryController
{
    public function __construct(private Shortlist $shortlists)
    {
    }

    public function history(int $csrId): array
    {
        return $this->shortlists->csrHistory($csrId);
    }
}
