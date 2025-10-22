<?php

namespace App\Admin\Controller;

use App\Core\Validator;
use App\Entity\UserAccount;

class CreateAccountController
{
    private ?string $errorMessage = null;
    private string $errorType = 'error';

    public function __construct(
        private Validator $validator,
        private UserAccount $userAccount
    ) {
    }

    public function createAccount(
        string $email,
        string $name,
        string $password,
        int|string $profileId,
        mixed $status = null
    ): bool {
        $this->errorMessage = null;
        $this->errorType = 'error';

        $payload = [
            'email' => trim($email),
            'name' => trim($name),
            'password' => trim($password),
            'profile_id' => is_numeric($profileId) ? (int) $profileId : 0,
        ];

        if (!$this->validator->validate($payload, [
            'email' => 'required|email',
            'name' => 'required|min:3',
            'password' => 'required|min:6',
            'profile_id' => 'required',
        ])) {
            $this->errorMessage = 'Please fill in all required fields before submitting.';
            return false;
        }

        if ($payload['profile_id'] <= 0) {
            $this->errorMessage = 'Please select a valid profile before submitting.';
            return false;
        }

        $statusValue = $this->normaliseStatus($status);

        if (!$this->userAccount->registerUserAccount(
            $payload['name'],
            $payload['email'],
            $payload['password'],
            $payload['profile_id'],
            $statusValue
        )) {
            $lastError = $this->userAccount->lastError();
            if ($lastError === 'duplicate_email') {
                $this->errorMessage = 'An account with this email already exists.';
                $this->errorType = 'warning';
            } elseif ($lastError === 'invalid_password') {
                $this->errorMessage = 'Passwords must be 8-20 characters long and include letters and numbers.';
            } elseif ($lastError === 'invalid_data') {
                $this->errorMessage = 'Unable to create account. Please review the provided data.';
            } else {
                $this->errorMessage = 'Unable to create account.';
            }
            return false;
        }

        return true;
    }

    public function errorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function errorType(): string
    {
        return $this->errorType;
    }

    private function normaliseStatus(mixed $status): string
    {
        if (is_bool($status)) {
            return $status ? 'active' : 'suspended';
        }

        if ($status === null) {
            return 'active';
        }

        $statusValue = trim((string) $status);
        return $statusValue === '' ? 'active' : $statusValue;
    }
}
