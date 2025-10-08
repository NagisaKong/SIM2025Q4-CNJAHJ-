<?php

namespace App\Models;

class Profile
{
    public function __construct(
        public int $id,
        public string $role,
        public string $description,
        public string $status,
        public string $created_at,
        public string $updated_at
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['id'],
            $data['role'],
            $data['description'],
            $data['status'],
            $data['created_at'],
            $data['updated_at']
        );
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
