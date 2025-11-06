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

    public function createRequest(int $pinId, int $serviceId, string $additionalDetails, array $options = []): bool
    {
        $payload = $options + [
            'service_id' => $serviceId,
            'additional_details' => $additionalDetails,
        ];

        return $this->persist($pinId, $payload);
    }

    public function create(int $pinId, array $input): bool
    {
        return $this->persist($pinId, $input);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function persist(int $pinId, array $input): bool
    {
        $this->errors = [];

        $sanitised = [
            'service_id' => $input['service_id'] ?? $input['category_id'] ?? $input['type'] ?? null,
            'additional_details' => $input['additional_details'] ?? $input['description'] ?? $input['additionalDetails'] ?? '',
            'title' => $input['title'] ?? null,
            'location' => $input['location'] ?? null,
            'requested_date' => $input['requested_date'] ?? null,
            'status' => $input['status'] ?? null,
        ];

        if (!$this->validator->validate($sanitised, [
            'service_id' => 'required|integer',
            'additional_details' => 'required|min:5',
        ])) {
            $this->errors = $this->validator->errors();
            return false;
        }

        return $this->requests->registerRequest(
            $pinId,
            (int) $sanitised['service_id'],
            (string) $sanitised['additional_details'],
            $sanitised['title'] !== null ? (string) $sanitised['title'] : null,
            $sanitised['location'] !== null ? (string) $sanitised['location'] : null,
            $sanitised['requested_date'] !== null ? (string) $sanitised['requested_date'] : null,
            $sanitised['status'] !== null ? (string) $sanitised['status'] : null
        );
    }
}
