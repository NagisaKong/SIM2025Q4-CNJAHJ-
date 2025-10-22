<?php

declare(strict_types=1);

namespace CSRPlatform\Admin\Controller;

use CSRPlatform\Shared\Entity\UserProfiles;

final class suspendProfileController
{
    public function __construct(private UserProfiles $profiles)
    {
    }

    public function suspend(int $profileId): bool
    {
        return $this->profiles->updateProfile($profileId, ['status' => 'suspended']);
    }
}
