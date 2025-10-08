<?php

namespace App\Core;

use App\Models\User;
use App\Repositories\UserRepository;

class Auth
{
    private const SESSION_KEY = 'user_id';

    public function __construct(private Session $session, private UserRepository $users)
    {
    }

    public function user(): ?User
    {
        $id = $this->session->get(self::SESSION_KEY);
        return $id ? $this->users->find((int) $id) : null;
    }

    public function attempt(string $email, string $password, ?string $expectedRole = null): bool
    {
        $user = $this->users->findByEmail($email);
        if (!$user || !$user->isActive()) {
            return false;
        }

        $profile = $user->profile;
        if ($profile && !$profile->isActive()) {
            return false;
        }

        if ($expectedRole !== null) {
            if ($profile === null || $profile->role !== $expectedRole) {
                return false;
            }
        }

        if (!password_verify($password, $user->password_hash)) {
            return false;
        }

        $this->session->put(self::SESSION_KEY, $user->id);
        $this->session->regenerate();
        return true;
    }

    public function logout(): void
    {
        $this->session->forget(self::SESSION_KEY);
        $this->session->regenerate();
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function hasRole(string $role): bool
    {
        $user = $this->user();
        return $user ? $user->profile?->role === $role : false;
    }
}
