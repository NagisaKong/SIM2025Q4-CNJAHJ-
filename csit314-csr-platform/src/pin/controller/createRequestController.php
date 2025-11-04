<?php

declare(strict_types=1);

namespace CSRPlatform\PIN\Controller;

use CSRPlatform\Shared\Entity\Request;
use CSRPlatform\Shared\Boundary\FormValidator;

final class createRequestController
{
    private array $errors = [];

    public function __construct(
        private Request $requests,
        private FormValidator $validator
    ) {
    }

    public function create(int $pinId, array $input): bool
    {
        $this->errors = [];
        if (!$this->validator->validate($input, [
            'category_id' => 'required|integer',
            'title' => 'required|min:3',
            'description' => 'required|min:10',
            'location' => 'required|min:3',
            'requested_date' => 'required',
        ])) {
            $this->errors = $this->validator->errors();
            return false;
        }

        return $this->requests->createRequest(
            $pinId,
            (int) $input['category_id'],
            $input['title'],
            $input['description'],
            $input['location'],
            $input['requested_date']
        );
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
