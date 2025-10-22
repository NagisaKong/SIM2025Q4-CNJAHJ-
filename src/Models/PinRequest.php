<?php

namespace App\Models;

class PinRequest
{
    public function __construct(
        public int $id,
        public int $pin_id,
        public int $category_id,
        public string $title,
        public string $description,
        public string $location,
        public string $status,
        public string $requested_date,
        public int $views_count,
        public int $shortlist_count,
        public string $created_at,
        public string $updated_at
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['id'],
            (int) $data['pin_id'],
            (int) $data['category_id'],
            $data['title'],
            $data['description'],
            $data['location'],
            $data['status'],
            $data['requested_date'],
            (int) $data['views_count'],
            (int) $data['shortlist_count'],
            $data['created_at'],
            $data['updated_at']
        );
    }
}
