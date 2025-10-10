<?php

namespace App\Entities;

use App\Models\User;
use App\Repositories\UserRepository;

class UserAccount
{
    private ?User $authenticatedUser = null;

    public function __construct(private UserRepository $users)
    {
    }

    public function validateUser(string $username, string $password, string $role): bool
    {
        $this->authenticatedUser = null;
        $user = $this->users->findByEmail($username);

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

    public function authenticatedUser(): ?User
    {
        return $this->authenticatedUser;
    }
}
