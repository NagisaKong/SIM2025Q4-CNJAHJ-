<?php

declare(strict_types=1);

namespace CSRPlatform\CSRRepresentative\Controller;

use CSRPlatform\Shared\Entity\Request;
use CSRPlatform\Shared\Entity\Shortlist;

final class saveRequestController
{
    public function __construct(
        private Shortlist $shortlists,
        private Request $requests
    ) {
    }

    public function saveRequest(int $accountId, int $requestId): bool
    {
        $saved = $this->shortlists->addToShortlist($accountId, $requestId);
        if ($saved) {
            $this->requests->increaseShortlistCount($requestId);
        }
        return $saved;
    }

    public function shortlist(int $csrId, int $requestId): bool
    {
        return $this->saveRequest($csrId, $requestId);
    }
}
