<?php

namespace App\Admin\Controller;

use App\Entity\UserProfile;
use App\Models\Profile;

class ViewProfilesController
{
    public function __construct(private UserProfile $userProfile)
    {
    }

    public function viewProfiles(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        return $this->userProfile->getUserProfileList($page, $perPage, $filters);
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
    public function describe(Profile $profile): array
    {
        return $this->userProfile->describeProfile($profile);
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
    public function describeCollection(iterable $profiles): array
    {
        return $this->userProfile->describeProfiles($profiles);
    }

    public function detail(int $id): ?array
    {
        return $this->userProfile->describeProfileById($id);
    }
}
