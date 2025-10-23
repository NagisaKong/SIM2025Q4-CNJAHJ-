<?php

declare(strict_types=1);

namespace CSRPlatform\Admin\Controller;

use CSRPlatform\Shared\Entity\UserAccount;
use CSRPlatform\Shared\Entity\UserProfiles;
use CSRPlatform\Shared\Utils\Validation;

final class createAccountController
{
    private array $errors = [];
    private ?string $message = null;

    public function __construct(
        private UserAccount $accounts,
        private UserProfiles $profiles,
        private Validation $validator
    ) {
    }

    public function create(array $input): bool
    {
        $this->errors = [];
        $this->message = null;
        if (!$this->validator->validate($input, [
            'role' => 'required',
            'name' => 'required|min:3',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ])) {
            $this->errors = $this->validator->errors();
            return false;
        }

        $status = $input['status'] ?? 'active';
        if (!$this->accounts->registerUA($input['role'], $input['name'], $input['email'], $input['password'], $status)) {
            $error = $this->accounts->lastError();
            $this->errors['account'][] = match ($error) {
                'duplicate_email' => 'Email already exists for another user.',
                'profile_missing' => 'Selected profile was not found.',
                'invalid_password' => 'Password must be 8-20 characters with letters and numbers.',
                default => 'Unable to create account.',
            };
            return false;
        }

        $this->message = 'Account created successfully.';
        return true;
    }

    public function profiles(): array
    {
        return $this->profiles->listProfiles('active');
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function message(): ?string
    {
        return $this->message;
    }
}
