<?php

declare(strict_types=1);

namespace CSRPlatform\CSRRepresentative\Controller;

use CSRPlatform\Shared\Entity\Shortlist;

final class searchCSRHistoryController
{
    public function __construct(private Shortlist $shortlists)
    {
    }

    public function search(int $csrId, string $query): array
    {
        $history = $this->shortlists->csrHistory($csrId);
        if (trim($query) === '') {
            return $history;
        }
        $query = strtolower($query);
        return array_values(array_filter($history, static function (array $row) use ($query): bool {
            return str_contains(strtolower((string) $row['title']), $query) ||
                str_contains(strtolower((string) ($row['status'] ?? '')), $query);
        }));
    }
}
