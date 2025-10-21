<?php

namespace App\Entities;

use App\Models\Profile;
use App\Repositories\ProfileRepository;

class UserProfile
{
    private ?string $lastError = null;
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $roleMetadata = [];

    public function __construct(private ProfileRepository $profiles)
    {
    }

    public function getUserProfileList(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        return $this->profiles->paginate($page, $perPage, $filters);
    }

    public function findProfile(int $id): ?Profile
    {
        return $this->profiles->find($id);
    }

    /**
     * @return array{
     *     profile: Profile,
     *     role: string,
     *     label: string,
     *     description: string,
     *     status: string,
     *     permissions: array<int, string>,
     *     information: string
     * }
     */
    public function describeProfile(Profile $profile): array
    {
        $metadata = $this->roleConfiguration()[$profile->role] ?? [];
        $label = $metadata['name'] ?? ucfirst(str_replace('_', ' ', $profile->role));
        $permissions = array_values(array_filter(
            $metadata['permissions'] ?? [],
            static fn ($permission): bool => is_string($permission) && $permission !== ''
        ));

        $information = $metadata['information'] ?? $profile->description;

        return [
            'profile' => $profile,
            'role' => $profile->role,
            'label' => $label,
            'description' => $profile->description,
            'status' => $profile->status,
            'permissions' => $permissions,
            'information' => $information,
        ];
    }

    public function describeProfileById(int $id): ?array
    {
        $profile = $this->findProfile($id);

        return $profile ? $this->describeProfile($profile) : null;
    }

    /**
     * @param iterable<Profile> $profiles
     * @return array<int, array{
     *     profile: Profile,
     *     role: string,
     *     label: string,
     *     description: string,
     *     status: string,
     *     permissions: array<int, string>,
     *     information: string
     * }>
     */
    public function describeProfiles(iterable $profiles): array
    {
        $descriptions = [];
        foreach ($profiles as $profile) {
            $descriptions[] = $this->describeProfile($profile);
        }

        return $descriptions;
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

    /**
     * @return array<string, array<string, mixed>>
     */
    private function roleConfiguration(): array
    {
        if ($this->roleMetadata === []) {
            $configPath = dirname(__DIR__, 2) . '/config/roles.php';
            if (is_file($configPath)) {
                $config = require $configPath;
                if (is_array($config)) {
                    $this->roleMetadata = $config;
                }
            }
        }

        return $this->roleMetadata;
    }
}
