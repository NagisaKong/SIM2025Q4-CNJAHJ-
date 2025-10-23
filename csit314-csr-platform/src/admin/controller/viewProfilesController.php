<?php

declare(strict_types=1);

namespace CSRPlatform\Admin\Controller;

use CSRPlatform\Shared\Entity\UserProfiles;

final class viewProfilesController
{
    public function __construct(private UserProfiles $profiles)
    {
    }

    public function list(?string $status = null, ?string $query = null): array
    {
        if ($query !== null && trim($query) !== '') {
            return $this->profiles->search($query);
        }
        return $this->profiles->listProfiles($status);
    }
}
