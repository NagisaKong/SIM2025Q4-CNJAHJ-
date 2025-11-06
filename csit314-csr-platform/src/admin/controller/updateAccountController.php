<?php

declare(strict_types=1);

namespace CSRPlatform\Admin\Controller;

use CSRPlatform\Shared\Entity\UserAccount;
use CSRPlatform\Shared\Boundary\FormValidator;

final class updateAccountController
{
    private array $errors = [];

    public function __construct(
        private UserAccount $accounts,
        private FormValidator $validator
    ) {
    }

    public function updateUserAccount(
        int $accountId,
        string $username,
        string $email,
        string $password,
        string $status,
        string $role
    ): bool {
        $this->errors = [];

        $validated = [
            'name' => trim($username),
            'email' => trim($email),
            'password' => $password,
        ];

        if (!$this->validator->validate(
            ['name' => $validated['name'], 'email' => $validated['email']],
            ['name' => 'required|min:3', 'email' => 'required|email']
        )) {
            $this->errors = $this->validator->errors();
            return false;
        }

        if ($status === '') {
            $this->errors['status'][] = 'Status is required.';
            return false;
        }

        if ($role === '') {
            $this->errors['role'][] = 'Role is required.';
            return false;
        }

        $passwordValue = trim($validated['password']);
        $passwordArg = $passwordValue === '' ? null : $passwordValue;

        if (!$this->accounts->updateUserAccount(
            $accountId,
            $validated['name'],
            $validated['email'],
            $passwordArg,
            $status,
            $role
        )) {
            $this->errors['account'][] = 'Unable to update account: ' . ($this->accounts->lastError() ?? 'unknown error');
            return false;
        }

        return true;
    }

    public function update(int $id, array $payload): bool
    {
        return $this->updateUserAccount(
            $id,
            (string) ($payload['name'] ?? ''),
            (string) ($payload['email'] ?? ''),
            (string) ($payload['password'] ?? ''),
            (string) ($payload['status'] ?? 'active'),
            (string) ($payload['role'] ?? '')
        );
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
