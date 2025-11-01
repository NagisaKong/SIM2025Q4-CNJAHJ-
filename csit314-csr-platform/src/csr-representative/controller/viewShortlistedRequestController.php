<?php

declare(strict_types=1);

namespace CSRPlatform\CSRRepresentative\Controller;

use CSRPlatform\Shared\Entity\Shortlist;

final class viewShortlistedRequestController
{
    public function __construct(private Shortlist $shortlists)
    {
    }

    public function list(int $csrId): array
    {
        return $this->shortlists->shortlistedRequests($csrId);
    }
}
