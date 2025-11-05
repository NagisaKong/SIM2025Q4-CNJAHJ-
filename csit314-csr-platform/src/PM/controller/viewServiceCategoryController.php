<?php

declare(strict_types=1);

namespace CSRPlatform\PM\Controller;

use CSRPlatform\Shared\Entity\ServiceCategories;

final class viewServiceCategoryController
{
    public function __construct(private ServiceCategories $categories)
    {
    }

    public function viewServiceCategory(string $serviceID): array
    {
        $category = $this->categories->getServiceCategory($serviceID);

        if ($category === null) {
            $this->recordError('Service category not found.');
            return [];
        }

        return $category;
    }

    public function listServiceCategories(?string $status = null, ?string $query = null): array
    {
        if ($query !== null && trim($query) !== '') {
            return $this->categories->search($query);
        }

        return $this->categories->listCategories($status);
    }

    /** @deprecated */
    public function list(?string $status = null, ?string $query = null): array
    {
        return $this->listServiceCategories($status, $query);
    }

    private function recordError(string $message): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['message_error'] = $message;
        $_SESSION['flash_error'] = $message;
    }
}
