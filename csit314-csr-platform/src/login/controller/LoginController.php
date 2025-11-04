<?php

declare(strict_types=1);

namespace CSRPlatform\Login\Controller;

use CSRPlatform\Shared\Entity\UserAccount;
use CSRPlatform\Shared\Boundary\FormValidator;

final class LoginController
{
    private array $errors = [];

    public function __construct(
        private UserAccount $accounts,
        private FormValidator $validator
    ) {
    }

    public function login(string $email, string $password, string $role): bool
    {
        $this->errors = [];
        if (!$this->validator->validate([
            'email' => $email,
            'password' => $password,
            'role' => $role,
        ], [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'role' => 'required',
        ])) {
            $this->errors = $this->validator->errors();
            return false;
        }

        if (!$this->accounts->validateUser($email, $password, $role)) {
            $this->errors['credentials'][] = 'Invalid credentials provided.';
            return false;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['user'] = $this->accounts->authenticatedUser();
        return true;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
