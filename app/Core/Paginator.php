<?php

namespace App\Core;

class Paginator
{
    public function __construct(
        public readonly array $items,
        public readonly int $total,
        public readonly int $perPage,
        public readonly int $currentPage
    ) {
    }

    public function lastPage(): int
    {
        return max(1, (int) ceil($this->total / $this->perPage));
    }

    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage();
    }
}
