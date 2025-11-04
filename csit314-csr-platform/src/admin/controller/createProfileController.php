<?php

declare(strict_types=1);

namespace CSRPlatform\Admin\Controller;

use CSRPlatform\Shared\Entity\UserProfiles;
use CSRPlatform\Shared\Boundary\FormValidator;

final class createProfileController
{
    private array $errors = [];

    public function __construct(
        private UserProfiles $profiles,
        private FormValidator $validator
    ) {
    }

    public function createUserProfile(string $role, string $description, string $status = 'active'): bool
    {
        $this->errors = [];
        $input = [
            'role' => $role,
            'description' => $description,
        ];

        if (!$this->validator->validate($input, [
            'role' => 'required|min:3',
            'description' => 'required|min:5',
        ])) {
            $this->errors = $this->validator->errors();
            return false;
        }

        $existing = $this->profiles->findByRole($role);
        if ($existing !== null) {
            $this->errors['role'][] = 'Role already exists.';
            return false;
        }

        if (!$this->profiles->registerUserProfile($role, $description, $status)) {
            $this->errors['profile'][] = 'Unable to create profile.';
            return false;
        }

        return true;
    }

    public function create(array $input): bool
    {
        return $this->createUserProfile(
            (string) ($input['role'] ?? ''),
            (string) ($input['description'] ?? ''),
            (string) ($input['status'] ?? 'active')
        );
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
