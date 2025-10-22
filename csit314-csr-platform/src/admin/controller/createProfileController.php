<?php
declare(strict_types=1);

use shared\entity\UserProfiles;
use shared\utils\Validation;

class CreateProfileController
{
    private UserProfiles $profiles;

    public function __construct()
    {
        $this->profiles = new UserProfiles();
    }

    public function create(array $input): bool
    {
        $role = Validation::sanitizeString($input['role'] ?? '');
        $description = Validation::sanitizeString($input['description'] ?? '');

        try {
            Validation::requireField($role, 'Role is required.');
            Validation::requireField($description, 'Description is required.');
        } catch (\InvalidArgumentException $exception) {
            $_SESSION['profile_message'] = $exception->getMessage();
            return false;
        }

        $this->profiles->createProfile($role, $description);
        $_SESSION['profile_message'] = 'Profile created successfully.';
        return true;
    }
}
