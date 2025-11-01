<?php

declare(strict_types=1);

namespace CSRPlatform\Admin\Controller;

use CSRPlatform\Shared\Entity\UserProfiles;

final class viewProfilesController
{
    public function __construct(private UserProfiles $profiles)
    {
    }

    public function viewUserProfileList(?string $status = null, ?string $query = null): array
    {
        if ($query !== null && trim($query) !== '') {
            return $this->profiles->searchProfileList($query);
        }

        return $this->profiles->getUserProfileList($status);
    }

    public function searchProfileList(string $query): array
    {
        return $this->profiles->searchProfileList($query);
    }

    public function viewUserProfile(int $profileId): ?array
    {
        return $this->profiles->getUserProfile($profileId);
    }

    /** @deprecated */
    public function list(?string $status = null, ?string $query = null): array
    {
        return $this->viewUserProfileList($status, $query);
    }
}
