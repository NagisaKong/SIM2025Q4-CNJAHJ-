<?php
declare(strict_types=1);

use shared\entity\UserProfiles;

class ViewProfilesController
{
    private UserProfiles $profiles;

    public function __construct()
    {
        $this->profiles = new UserProfiles();
    }

    public function list(): array
    {
        return $this->profiles->listProfiles();
    }
}
