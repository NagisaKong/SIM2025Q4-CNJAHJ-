<?php

declare(strict_types=1);

namespace CSRPlatform\CSRRepresentative\Controller;

use CSRPlatform\Shared\Entity\Shortlist;

final class searchShortlistController
{
    public function __construct(private Shortlist $shortlists)
    {
    }

    public function search(int $csrId, string $query): array
    {
        return $this->shortlists->searchShortlist($csrId, $query);
    }
}
