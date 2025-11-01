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

    public function shortlist(int $csrId, int $requestId): bool
    {
        $saved = $this->shortlists->addToShortlist($csrId, $requestId);
        if ($saved) {
            $this->requests->updateShortlistCount($requestId);
        }
        return $saved;
    }
}
