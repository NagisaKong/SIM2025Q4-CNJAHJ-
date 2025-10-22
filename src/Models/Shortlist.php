<?php

namespace App\Models;

class Shortlist
{
    public function __construct(
        public int $id,
        public int $csr_id,
        public int $request_id,
        public string $created_at
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['id'],
            (int) $data['csr_id'],
            (int) $data['request_id'],
            $data['created_at']
        );
    }
}
