<?php
declare(strict_types=1);

use shared\entity\UserAccount;
use shared\utils\Validation;

class CreateAccountController
{
    private UserAccount $accounts;

    public function __construct()
    {
        $this->accounts = new UserAccount();
    }

    public function create(array $input): bool
    {
        $name = Validation::sanitizeString($input['name'] ?? '');
        $email = Validation::sanitizeString($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $role = Validation::sanitizeString($input['role'] ?? '');

        try {
            Validation::requireField($name, 'Name is required.');
            Validation::requireField($email, 'Email is required.');
            Validation::email($email);
            Validation::requireField($password, 'Password is required.');
            Validation::requireField($role, 'Role is required.');
        } catch (\InvalidArgumentException $exception) {
            $_SESSION['registration_message'] = $exception->getMessage();
            return false;
        }

        if (!$this->accounts->validateUA($role, $email, $password)) {
            $_SESSION['registration_message'] = 'Email is already used or password is invalid.';
            return false;
        }

        $this->accounts->registerUA($role, $name, $email, $password);
        $_SESSION['registration_message'] = 'Account created successfully.';
        return true;
    }
}
