<?php

declare(strict_types=1);

namespace CSRPlatform\Admin\Controller;

use CSRPlatform\Shared\Entity\UserProfiles;

final class suspendProfileController
{
    public function __construct(private UserProfiles $profiles)
    {
    }

    public function suspendUserProfile(int $profileId, string $status = 'suspended'): bool
    {
        return $this->profiles->holdUserProfile($profileId, $status);
    }

    /** @deprecated */
    public function suspend(int $profileId): bool
    {
        return $this->suspendUserProfile($profileId);
    }
}
