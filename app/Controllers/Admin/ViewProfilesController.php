<?php

namespace App\Controllers\Admin;

use App\Entities\UserProfile;

class ViewProfilesController
{
    public function __construct(private UserProfile $userProfile)
    {
    }

    public function viewProfiles(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        return $this->userProfile->getUserProfileList($page, $perPage, $filters);
    }
}
