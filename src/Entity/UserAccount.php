<?php

namespace App\Entity;

use App\Models\User;
use App\Repositories\UserRepository;

class UserAccount
{
    private ?User $authenticatedUser = null;
    private ?string $lastError = null;

    public function __construct(private UserRepository $users)
    {
    }

    public function validateUser(string $email, string $password, string $role): bool
    {
        $this->lastError = null;
        $this->authenticatedUser = null;
        $user = $this->users->findByEmail($email);

        if ($user === null || !$user->isActive()) {
            return false;
        }

        $profile = $user->profile;
        if ($profile === null || !$profile->isActive()) {
            return false;
        }

        if ($profile->role !== $role) {
            return false;
        }

        if (!password_verify($password, $user->password_hash)) {
            return false;
        }

        $this->authenticatedUser = $user;
        return true;
    }

    public function validatePassword(string $password): bool
    {
        $password = trim($password);
        $pattern = '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,20}$/';

        return preg_match($pattern, $password) === 1;
    }

    public function authenticatedUser(): ?User
    {
        return $this->authenticatedUser;
    }

    public function registerUserAccount(string $name, string $email, string $password, int $profileId, string $status = 'active'): bool
    {
        $this->lastError = null;

        $name = trim($name);
        $email = strtolower(trim($email));
        $password = trim($password);
        $status = trim($status);

        if ($name === '' || $email === '' || $password === '' || $profileId <= 0) {
            $this->lastError = 'invalid_data';
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->lastError = 'invalid_data';
            return false;
        }

        if (!$this->validatePassword($password)) {
            $this->lastError = 'invalid_password';
            return false;
        }

        if ($this->users->findByEmail($email) !== null) {
            $this->lastError = 'duplicate_email';
            return false;
        }

        $this->users->create([
            'profile_id' => $profileId,
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'status' => $status === '' ? 'active' : $status,
        ]);

        return true;
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }
}
