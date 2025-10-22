<?php

namespace App\Admin\Controller;

use App\Core\Validator;
use App\Entity\UserProfile;

class CreateProfileController
{
    private ?string $errorMessage = null;
    private string $errorType = 'error';

    public function __construct(
        private Validator $validator,
        private UserProfile $userProfile
    ) {
    }

    public function createProfile(string $role, string $description, ?string $status = null): bool
    {
        $this->errorMessage = null;
        $this->errorType = 'error';

        $payload = [
            'role' => trim($role),
            'description' => trim($description),
        ];

        if (!$this->validator->validate($payload, [
            'role' => 'required',
            'description' => 'required|min:3',
        ])) {
            $this->errorMessage = 'Please fill in all required fields before submitting.';
            return false;
        }

        $statusValue = $status !== null ? trim($status) : 'active';
        if ($statusValue === '') {
            $statusValue = 'active';
        }

        if (!$this->userProfile->registerUserProfile($payload['role'], $payload['description'], $statusValue)) {
            if ($this->userProfile->lastError() === 'duplicate_role') {
                $this->errorMessage = 'A profile for this role already exists.';
                $this->errorType = 'warning';
            } else {
                $this->errorMessage = 'Unable to create profile.';
                $this->errorType = 'error';
            }
            return false;
        }

        return true;
    }

    public function errorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function errorType(): string
    {
        return $this->errorType;
    }
}
