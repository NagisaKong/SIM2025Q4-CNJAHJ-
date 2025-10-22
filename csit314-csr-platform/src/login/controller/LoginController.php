<?php
declare(strict_types=1);

use shared\entity\UserAccount;
use shared\database\DatabaseConnection;
use shared\database\DataGenerator;

class LoginController
{
    private UserAccount $userAccount;

    public function __construct()
    {
        DatabaseConnection::get();
        DataGenerator::migrateAndSeed();
        $this->userAccount = new UserAccount();
    }

    public function authenticate(string $email, string $password, string $role): bool
    {
        if (!$this->userAccount->validateUser($email, $password, $role)) {
            $_SESSION['login_error'] = 'Invalid credentials or inactive account.';
            return false;
        }
        $user = $this->userAccount->getUserData($role, $email);
        $_SESSION['user'] = $user;
        $_SESSION['user_role'] = $role;
        return true;
    }
}
