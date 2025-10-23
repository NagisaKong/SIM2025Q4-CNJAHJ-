<?php

declare(strict_types=1);

namespace CSRPlatform\Admin\Controller;

use CSRPlatform\Shared\Entity\UserProfiles;
use CSRPlatform\Shared\Utils\Validation;

final class updateProfileController
{
    private array $errors = [];

    public function __construct(
        private UserProfiles $profiles,
        private Validation $validator
    ) {
    }

    public function update(int $id, array $payload): bool
    {
        $this->errors = [];

        $data = [
            'role' => strtolower(trim((string) ($payload['role'] ?? ''))),
            'description' => trim((string) ($payload['description'] ?? '')),
            'status' => strtolower(trim((string) ($payload['status'] ?? ''))),
        ];

        $rules = [
            'role' => 'required|min:2',
            'description' => 'required|min:5',
        ];

        if (!$this->validator->validate($data, $rules)) {
            $this->errors = $this->validator->errors();
            return false;
        }

        if ($data['status'] === '') {
            $this->errors['status'][] = 'Status is required.';
            return false;
        }

        if (!in_array($data['status'], ['active', 'suspended'], true)) {
            $this->errors['status'][] = 'Status must be active or suspended.';
            return false;
        }

        $updatePayload = [
            'role' => $data['role'],
            'description' => $data['description'],
            'status' => $data['status'],
        ];

        if (!$this->profiles->updateProfile($id, $updatePayload)) {
            $this->errors['profile'][] = 'Unable to update profile.';
            return false;
        }

        return true;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
