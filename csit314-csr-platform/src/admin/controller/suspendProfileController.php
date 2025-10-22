<?php
declare(strict_types=1);

use shared\entity\UserProfiles;

class SuspendProfileController
{
    private UserProfiles $profiles;

    public function __construct()
    {
        $this->profiles = new UserProfiles();
    }

    public function suspend(int $id): bool
    {
        $success = $this->profiles->suspendProfile($id);
        $_SESSION['profile_message'] = $success ? 'Profile suspended successfully.' : 'Unable to suspend profile.';
        return $success;
    }
}
