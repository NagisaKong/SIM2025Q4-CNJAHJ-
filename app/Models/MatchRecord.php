<?php

namespace App\Models;

class MatchRecord
{
    public function __construct(
        public int $id,
        public int $csr_id,
        public int $request_id,
        public string $status,
        public string $matched_at,
        public ?string $completed_at
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['id'],
            (int) $data['csr_id'],
            (int) $data['request_id'],
            $data['status'],
            $data['matched_at'],
            $data['completed_at'] ?? null
        );
    }
}
