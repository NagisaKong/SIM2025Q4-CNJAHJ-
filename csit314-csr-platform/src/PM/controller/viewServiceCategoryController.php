<?php

declare(strict_types=1);

namespace CSRPlatform\PM\Controller;

use CSRPlatform\Shared\Entity\ServiceCategories;

final class viewServiceCategoryController
{
    public function __construct(private ServiceCategories $categories)
    {
    }

    public function list(?string $status = null, ?string $query = null): array
    {
        if ($query !== null && trim($query) !== '') {
            return $this->categories->search($query);
        }
        return $this->categories->listCategories($status);
    }
}
