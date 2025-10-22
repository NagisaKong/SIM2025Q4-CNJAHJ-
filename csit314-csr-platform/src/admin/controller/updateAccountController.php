<?php
declare(strict_types=1);

use shared\entity\UserAccount;
use shared\utils\Validation;

class UpdateAccountController
{
    private UserAccount $accounts;

    public function __construct()
    {
        $this->accounts = new UserAccount();
    }

    public function update(int $id, array $input): bool
    {
        $email = Validation::sanitizeString($input['email'] ?? '');
        $role = Validation::sanitizeString($input['role'] ?? '');
        $status = Validation::sanitizeString($input['status'] ?? 'active');

        try {
            Validation::requireField($email, 'Email is required.');
            Validation::email($email);
            Validation::requireField($role, 'Role is required.');
            Validation::requireField($status, 'Status is required.');
        } catch (\InvalidArgumentException $exception) {
            $_SESSION['registration_message'] = $exception->getMessage();
            return false;
        }

        $success = $this->accounts->updateAccountDetails($id, $role, $email, $status);
        if ($success) {
            $_SESSION['registration_message'] = 'Account updated successfully.';
        } else {
            $_SESSION['registration_message'] = 'Unable to update account. Please try again.';
        }
        return $success;
    }

    public function find(int $id): array
    {
        return $this->accounts->getUserAccountList($id);
    }
}
