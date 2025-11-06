<?php

declare(strict_types=1);

namespace CSRPlatform\Admin\Controller;

use CSRPlatform\Shared\Entity\UserProfiles;
use CSRPlatform\Shared\Boundary\FormValidator;

final class updateProfileController
{
    private array $errors = [];

    public function __construct(
        private UserProfiles $profiles,
        private FormValidator $validator
    ) {
    }

    public function updateUserProfile(int $profileId, string $role, string $description, string $status): bool
    {
        $this->errors = [];

        $normalized = [
            'role' => strtolower(trim($role)),
            'description' => trim($description),
            'status' => strtolower(trim($status)),
        ];

        if (!$this->validator->validate(
            ['role' => $normalized['role'], 'description' => $normalized['description']],
            ['role' => 'required|min:2', 'description' => 'required|min:5']
        )) {
            $this->errors = $this->validator->errors();
            return false;
        }

        if ($normalized['status'] === '') {
            $this->errors['status'][] = 'Status is required.';
            return false;
        }

        if (!in_array($normalized['status'], ['active', 'suspended'], true)) {
            $this->errors['status'][] = 'Status must be active or suspended.';
            return false;
        }

        if (!$this->profiles->updateUserProfile($profileId, $normalized['role'], $normalized['description'], $normalized['status'])) {
            $this->errors['profile'][] = 'Unable to update profile.';
            return false;
        }

        return true;
    }

    public function update(int $id, array $payload): bool
    {
        return $this->updateUserProfile(
            $id,
            (string) ($payload['role'] ?? ''),
            (string) ($payload['description'] ?? ''),
            (string) ($payload['status'] ?? '')
        );
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
