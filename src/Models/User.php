<?php

namespace App\Models;

class User
{
    public function __construct(
        public int $id,
        public int $profile_id,
        public string $name,
        public string $email,
        public string $password_hash,
        public string $status,
        public string $created_at,
        public string $updated_at,
        public ?Profile $profile = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['id'],
            (int) $data['profile_id'],
            $data['name'],
            $data['email'],
            $data['password_hash'],
            $data['status'],
            $data['created_at'],
            $data['updated_at'],
        );
    }

    public function withProfile(?Profile $profile): self
    {
        $this->profile = $profile;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
