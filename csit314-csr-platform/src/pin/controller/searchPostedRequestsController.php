<?php

declare(strict_types=1);

namespace CSRPlatform\PIN\Controller;

use CSRPlatform\Shared\Entity\Request;

final class searchPostedRequestsController
{
    public function __construct(private Request $requests)
    {
    }

    public function search(int $pinId, ?string $query = null): array
    {
        $all = $this->requests->listRequestsByPin($pinId);
        if ($query === null || trim($query) === '') {
            return $all;
        }
        $query = strtolower($query);
        return array_values(array_filter($all, static function (array $row) use ($query): bool {
            return str_contains(strtolower((string) $row['title']), $query) ||
                str_contains(strtolower((string) $row['description']), $query);
        }));
    }
}
