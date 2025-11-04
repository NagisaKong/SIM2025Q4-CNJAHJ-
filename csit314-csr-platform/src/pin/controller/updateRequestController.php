<?php

declare(strict_types=1);

namespace CSRPlatform\PIN\Controller;

use CSRPlatform\Shared\Boundary\FormValidator;
use CSRPlatform\Shared\Entity\Request;

final class updateRequestController
{
    private array $errors = [];

    public function __construct(
        private Request $requests,
        private FormValidator $validator
    ) {
    }

    public function updateRequest(
        int $pinId,
        int $requestId,
        int $serviceId,
        string $additionalDetails,
        string $status,
        array $options = []
    ): bool {
        $payload = $options + [
            'service_id' => $serviceId,
            'additional_details' => $additionalDetails,
            'status' => $status,
        ];

        return $this->persist($pinId, $requestId, $payload);
    }

    public function update(int $pinId, int $requestId, array $input): bool
    {
        return $this->persist($pinId, $requestId, $input);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function persist(int $pinId, int $requestId, array $input): bool
    {
        $this->errors = [];

        $sanitised = [
            'service_id' => $input['service_id'] ?? $input['category_id'] ?? null,
            'additional_details' => $input['additional_details'] ?? $input['description'] ?? '',
            'status' => $input['status'] ?? null,
            'title' => $input['title'] ?? null,
            'location' => $input['location'] ?? null,
            'requested_date' => $input['requested_date'] ?? null,
        ];

        if (!$this->validator->validate($sanitised, [
            'service_id' => 'required|integer',
            'additional_details' => 'required|min:5',
            'status' => 'required',
        ])) {
            $this->errors = $this->validator->errors();
            return false;
        }

        return $this->requests->updateRequestDetails(
            $pinId,
            $requestId,
            (int) $sanitised['service_id'],
            (string) $sanitised['additional_details'],
            (string) $sanitised['status'],
            $sanitised['title'] !== null ? (string) $sanitised['title'] : null,
            $sanitised['location'] !== null ? (string) $sanitised['location'] : null,
            $sanitised['requested_date'] !== null ? (string) $sanitised['requested_date'] : null
        );
    }
}
