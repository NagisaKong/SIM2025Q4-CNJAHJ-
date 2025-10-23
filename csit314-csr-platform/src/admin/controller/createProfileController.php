<?php

declare(strict_types=1);

namespace CSRPlatform\Admin\Controller;

use CSRPlatform\Shared\Entity\UserProfiles;
use CSRPlatform\Shared\Utils\Validation;

final class createProfileController
{
    private array $errors = [];

    public function __construct(
        private UserProfiles $profiles,
        private Validation $validator
    ) {
    }

    public function create(array $input): bool
    {
        $this->errors = [];
        if (!$this->validator->validate($input, [
            'role' => 'required|min:3',
            'description' => 'required|min:5',
        ])) {
            $this->errors = $this->validator->errors();
            return false;
        }

        $existing = $this->profiles->findByRole($input['role']);
        if ($existing !== null) {
            $this->errors['role'][] = 'Role already exists.';
            return false;
        }

        return $this->profiles->createProfile($input['role'], $input['description']);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
