<?php

namespace App\Models;

class ServiceCategory
{
    public function __construct(
        public int $id,
        public string $name,
        public string $status,
        public string $created_at,
        public string $updated_at
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['id'],
            $data['name'],
            $data['status'],
            $data['created_at'],
            $data['updated_at']
        );
    }
}
