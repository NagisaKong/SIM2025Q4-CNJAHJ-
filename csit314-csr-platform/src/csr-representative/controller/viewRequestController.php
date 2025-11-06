<?php

declare(strict_types=1);

namespace CSRPlatform\CSRRepresentative\Controller;

use CSRPlatform\Shared\Entity\Request;

final class viewRequestController
{
    public function __construct(private Request $requests)
    {
    }

    public function viewRequest(int $requestId): ?array
    {
        if ($requestId <= 0) {
            return null;
        }

        $request = $this->requests->find($requestId);
        if ($request === null) {
            return null;
        }

        $this->requests->incrementView($requestId);
        if (array_key_exists('views_count', $request)) {
            $request['views_count'] = (int) $request['views_count'] + 1;
        }

        return $request;
    }

    public function show(int $requestId): ?array
    {
        return $this->viewRequest($requestId);
    }
}
