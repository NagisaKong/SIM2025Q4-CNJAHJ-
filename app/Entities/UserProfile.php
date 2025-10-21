<?php

namespace App\Entities;

use App\Repositories\ProfileRepository;

class UserProfile
{
    private ?string $lastError = null;

    public function __construct(private ProfileRepository $profiles)
    {
    }

    public function getUserProfileList(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        return $this->profiles->paginate($page, $perPage, $filters);
    }

    public function registerUserProfile(string $role, string $description, string $status = 'active'): bool
    {
        $this->lastError = null;

        $role = trim($role);
        $description = trim($description);
        $status = trim($status);

        if ($role === '' || $description === '') {
            $this->lastError = 'invalid_data';
            return false;
        }

        if ($this->profiles->findByRole($role) !== null) {
            $this->lastError = 'duplicate_role';
            return false;
        }

        $this->profiles->create([
            'role' => $role,
            'description' => $description,
            'status' => $status === '' ? 'active' : $status,
        ]);

        return true;
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }
}
